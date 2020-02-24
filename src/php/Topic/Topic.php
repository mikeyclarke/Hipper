<?php
declare(strict_types=1);

namespace Hipper\Topic;

use Doctrine\DBAL\Connection;
use Hipper\Document\Exception\KnowledgebaseNotFoundException;
use Hipper\Document\Exception\MissingRouteException;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Knowledgebase\KnowledgebaseModel;
use Hipper\Knowledgebase\KnowledgebaseOwner;
use Hipper\Knowledgebase\KnowledgebaseRepository;
use Hipper\Knowledgebase\KnowledgebaseRoute;
use Hipper\Knowledgebase\KnowledgebaseRouteModel;
use Hipper\Knowledgebase\KnowledgebaseRouteRepository;
use Hipper\Organization\Exception\ResourceIsForeignToOrganizationException;
use Hipper\Person\PersonModel;
use Hipper\Url\UrlIdGenerator;
use Hipper\Url\UrlSlugGenerator;

class Topic
{
    private Connection $connection;
    private IdGenerator $idGenerator;
    private KnowledgebaseOwner $knowledgebaseOwner;
    private KnowledgebaseRepository $knowledgebaseRepository;
    private KnowledgebaseRoute $knowledgebaseRoute;
    private KnowledgebaseRouteRepository $knowledgebaseRouteRepository;
    private TopicInserter $topicInserter;
    private TopicRepository $topicRepository;
    private TopicUpdater $topicUpdater;
    private TopicValidator $topicValidator;
    private UpdateTopicDescendantRoutes $updateTopicDescendantRoutes;
    private UrlIdGenerator $urlIdGenerator;
    private UrlSlugGenerator $urlSlugGenerator;

    public function __construct(
        Connection $connection,
        IdGenerator $idGenerator,
        KnowledgebaseOwner $knowledgebaseOwner,
        KnowledgebaseRepository $knowledgebaseRepository,
        KnowledgebaseRoute $knowledgebaseRoute,
        KnowledgebaseRouteRepository $knowledgebaseRouteRepository,
        TopicInserter $topicInserter,
        TopicRepository $topicRepository,
        TopicUpdater $topicUpdater,
        TopicValidator $topicValidator,
        UpdateTopicDescendantRoutes $updateTopicDescendantRoutes,
        UrlIdGenerator $urlIdGenerator,
        UrlSlugGenerator $urlSlugGenerator
    ) {
        $this->connection = $connection;
        $this->idGenerator = $idGenerator;
        $this->knowledgebaseOwner = $knowledgebaseOwner;
        $this->knowledgebaseRepository = $knowledgebaseRepository;
        $this->knowledgebaseRoute = $knowledgebaseRoute;
        $this->knowledgebaseRouteRepository = $knowledgebaseRouteRepository;
        $this->topicInserter = $topicInserter;
        $this->topicRepository = $topicRepository;
        $this->topicUpdater = $topicUpdater;
        $this->topicValidator = $topicValidator;
        $this->updateTopicDescendantRoutes = $updateTopicDescendantRoutes;
        $this->urlIdGenerator = $urlIdGenerator;
        $this->urlSlugGenerator = $urlSlugGenerator;
    }

    public function create(PersonModel $person, array $parameters): array
    {
        $organizationId = $person->getOrganizationId();
        $knowledgebase = $this->getKnowledgebase($parameters, $organizationId);
        $parentTopic = $this->getParentTopic(
            $parameters['parent_topic_id'] ?? null,
            $knowledgebase,
            $organizationId
        );
        $this->topicValidator->validate($parameters, $knowledgebase, $parentTopic, true);

        $id = $this->idGenerator->generate();
        $urlSlug = $this->urlSlugGenerator->generateFromString($parameters['name']);
        $urlId = $this->urlIdGenerator->generate();

        $this->connection->beginTransaction();
        try {
            $result = $this->topicInserter->insert(
                $id,
                $parameters['name'],
                $urlSlug,
                $urlId,
                $parameters['knowledgebase_id'],
                $person->getOrganizationId(),
                $parameters['description'] ?? null,
                $parameters['parent_topic_id'] ?? null
            );

            $model = TopicModel::createFromArray($result);

            $routePrefix = $this->getRoutePrefix($organizationId, $parameters['knowledgebase_id'], $parentTopic);
            $route = $this->knowledgebaseRoute->create(
                $model,
                $routePrefix . $model->getUrlSlug(),
                true,
                true
            );

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

        $knowledgebaseOwnerModel = $this->knowledgebaseOwner->get($knowledgebase);

        return [$model, $route, $knowledgebaseOwnerModel];
    }

    public function update(PersonModel $person, TopicModel $topic, array $parameters): array
    {
        if ($person->getOrganizationId() !== $topic->getOrganizationId()) {
            throw new ResourceIsForeignToOrganizationException(sprintf(
                'Topic with ID “%d” does not belong to current user’s organization',
                $topic->getId()
            ));
        }

        $organizationId = $topic->getOrganizationId();
        $knowledgebaseId = $topic->getKnowledgebaseId();

        $result = $this->knowledgebaseRepository->findById($knowledgebaseId, $organizationId);
        if (null === $result) {
            throw new KnowledgebaseNotFoundException;
        }
        $knowledgebase = KnowledgebaseModel::createFromArray($result);
        $knowledgebaseOwnerModel = $this->knowledgebaseOwner->get($knowledgebase);
        $parentTopic = $this->getParentTopic(
            $parameters['parent_topic_id'] ?? null,
            $knowledgebase,
            $organizationId
        );

        $this->topicValidator->validate($parameters, null, $parentTopic);

        $propertiesToUpdate = [];

        if (isset($parameters['name']) && $parameters['name'] !== $topic->getName()) {
            $propertiesToUpdate['name'] = $parameters['name'];

            $urlSlug = $this->urlSlugGenerator->generateFromString($parameters['name']);
            if ($urlSlug !== $topic->getUrlSlug()) {
                $propertiesToUpdate['url_slug'] = $urlSlug;
            }
        }

        if (array_key_exists('description', $parameters) &&
            $parameters['description'] !== $topic->getDescription()
        ) {
            $propertiesToUpdate['description'] = $parameters['description'];
        }

        if (array_key_exists('parent_topic_id', $parameters) &&
            $parameters['parent_topic_id'] !== $topic->getParentTopicId()
        ) {
            $propertiesToUpdate['parent_topic_id'] = $parameters['parent_topic_id'];
        }

        $routeHasChanged = isset($propertiesToUpdate['url_slug']) ||
            array_key_exists('parent_topic_id', $propertiesToUpdate);

        $route = null;
        if (!$routeHasChanged) {
            $routeResult = $this->knowledgebaseRouteRepository->findCanonicalRouteForTopic(
                $organizationId,
                $knowledgebaseId,
                $topic->getId()
            );
            if (null === $routeResult) {
                throw new MissingRouteException('Topic has no canonical route');
            }

            $route = KnowledgebaseRouteModel::createFromArray($routeResult);
        }

        if (empty($propertiesToUpdate)) {
            return [$topic, $route, $knowledgebaseOwnerModel];
        }

        if (!array_key_exists('parent_topic_id', $propertiesToUpdate) && $routeHasChanged) {
            $parentTopic = $this->getParentTopic($topic->getParentTopicId(), $knowledgebase, $organizationId);
        }

        $this->connection->beginTransaction();
        try {
            $result = $this->topicUpdater->update(
                $topic->getId(),
                $propertiesToUpdate
            );
            $topic->updateFromArray($result);

            if ($routeHasChanged) {
                $routePrefix = $this->getRoutePrefix($organizationId, $knowledgebaseId, $parentTopic);
                $route = $this->knowledgebaseRoute->create(
                    $topic,
                    $routePrefix . $topic->getUrlSlug(),
                    true
                );
            }

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

        if ($routeHasChanged) {
            $this->updateTopicDescendantRoutes->update($topic, $route);
        }

        return [$topic, $route, $knowledgebaseOwnerModel];
    }

    private function getKnowledgebase(array $parameters, string $organizationId): ?KnowledgebaseModel
    {
        if (!isset($parameters['knowledgebase_id'])) {
            return null;
        }

        $result = $this->knowledgebaseRepository->findById($parameters['knowledgebase_id'], $organizationId);
        if (null == $result) {
            return null;
        }

        return KnowledgebaseModel::createFromArray($result);
    }

    private function getParentTopic(
        ?string $parentTopicId,
        ?KnowledgebaseModel $knowledgebase,
        string $organizationId
    ): ?TopicModel {
        if (null === $parentTopicId) {
            return null;
        }

        if (!$knowledgebase instanceof KnowledgebaseModel) {
            return null;
        }

        $result = $this->topicRepository->findByIdInKnowledgebase(
            $parentTopicId,
            $knowledgebase->getId(),
            $organizationId
        );
        if (null === $result) {
            return null;
        }

        return TopicModel::createFromArray($result);
    }

    private function getRoutePrefix(string $organizationId, string $knowledgebaseId, ?TopicModel $topic): string
    {
        if (null === $topic) {
            return '';
        }

        $result = $this->knowledgebaseRouteRepository->findCanonicalRouteForTopic(
            $organizationId,
            $knowledgebaseId,
            $topic->getId()
        );

        if (null === $result) {
            throw new MissingRouteException(sprintf(
                'Cannot create topic because parent topic %s is missing a canonical knowledgebase route',
                $topic->getId()
            ));
        }

        return $result['route'] . '/';
    }
}
