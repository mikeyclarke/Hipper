<?php
declare(strict_types=1);

namespace Hipper\Topic;

use Doctrine\DBAL\Connection;
use Hipper\Document\Exception\MissingRouteException;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Knowledgebase\KnowledgebaseModel;
use Hipper\Knowledgebase\KnowledgebaseOwner;
use Hipper\Knowledgebase\KnowledgebaseRepository;
use Hipper\Knowledgebase\KnowledgebaseRouteCreator;
use Hipper\Knowledgebase\KnowledgebaseRouteRepository;
use Hipper\Person\PersonModel;
use Hipper\Topic\Event\TopicCreatedEvent;
use Hipper\Topic\Storage\TopicInserter;
use Hipper\Url\UrlIdGenerator;
use Hipper\Url\UrlSlugGenerator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TopicCreator
{
    private Connection $connection;
    private EventDispatcherInterface $eventDispatcher;
    private IdGenerator $idGenerator;
    private KnowledgebaseOwner $knowledgebaseOwner;
    private KnowledgebaseRepository $knowledgebaseRepository;
    private KnowledgebaseRouteCreator $knowledgebaseRouteCreator;
    private KnowledgebaseRouteRepository $knowledgebaseRouteRepository;
    private TopicInserter $topicInserter;
    private TopicRepository $topicRepository;
    private TopicValidator $topicValidator;
    private UpdateTopicDescendantRoutes $updateTopicDescendantRoutes;
    private UrlIdGenerator $urlIdGenerator;
    private UrlSlugGenerator $urlSlugGenerator;

    public function __construct(
        Connection $connection,
        EventDispatcherInterface $eventDispatcher,
        IdGenerator $idGenerator,
        KnowledgebaseOwner $knowledgebaseOwner,
        KnowledgebaseRepository $knowledgebaseRepository,
        KnowledgebaseRouteCreator $knowledgebaseRouteCreator,
        KnowledgebaseRouteRepository $knowledgebaseRouteRepository,
        TopicInserter $topicInserter,
        TopicRepository $topicRepository,
        TopicValidator $topicValidator,
        UpdateTopicDescendantRoutes $updateTopicDescendantRoutes,
        UrlIdGenerator $urlIdGenerator,
        UrlSlugGenerator $urlSlugGenerator
    ) {
        $this->connection = $connection;
        $this->eventDispatcher = $eventDispatcher;
        $this->idGenerator = $idGenerator;
        $this->knowledgebaseOwner = $knowledgebaseOwner;
        $this->knowledgebaseRepository = $knowledgebaseRepository;
        $this->knowledgebaseRouteCreator = $knowledgebaseRouteCreator;
        $this->knowledgebaseRouteRepository = $knowledgebaseRouteRepository;
        $this->topicInserter = $topicInserter;
        $this->topicRepository = $topicRepository;
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
            $route = $this->knowledgebaseRouteCreator->create(
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

        $event = new TopicCreatedEvent($model, $knowledgebaseOwnerModel, $route, $person);
        $this->eventDispatcher->dispatch($event, TopicCreatedEvent::NAME);

        return [$model, $route, $knowledgebaseOwnerModel];
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
