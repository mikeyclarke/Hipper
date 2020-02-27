<?php
declare(strict_types=1);

namespace Hipper\Document;

use Doctrine\DBAL\Connection;
use Hipper\Document\Exception\MissingRouteException;
use Hipper\Document\Storage\DocumentInserter;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Knowledgebase\KnowledgebaseModel;
use Hipper\Knowledgebase\KnowledgebaseOwner;
use Hipper\Knowledgebase\KnowledgebaseRepository;
use Hipper\Knowledgebase\KnowledgebaseRoute;
use Hipper\Knowledgebase\KnowledgebaseRouteModel;
use Hipper\Knowledgebase\KnowledgebaseRouteRepository;
use Hipper\Person\PersonModel;
use Hipper\Topic\TopicModel;
use Hipper\Topic\TopicRepository;
use Hipper\Url\UrlIdGenerator;
use Hipper\Url\UrlSlugGenerator;
use JSON_THROW_ON_ERROR;

class DocumentCreator
{
    private Connection $connection;
    private DocumentDescriptionDeducer $documentDescriptionDeducer;
    private DocumentInserter $documentInserter;
    private DocumentRenderer $documentRenderer;
    private DocumentRevisionCreator $documentRevisionCreator;
    private DocumentValidator $documentValidator;
    private IdGenerator $idGenerator;
    private KnowledgebaseOwner $knowledgebaseOwner;
    private KnowledgebaseRepository $knowledgebaseRepository;
    private KnowledgebaseRoute $knowledgebaseRoute;
    private KnowledgebaseRouteRepository $knowledgebaseRouteRepository;
    private TopicRepository $topicRepository;
    private UrlIdGenerator $urlIdGenerator;
    private UrlSlugGenerator $urlSlugGenerator;

    public function __construct(
        Connection $connection,
        DocumentDescriptionDeducer $documentDescriptionDeducer,
        DocumentInserter $documentInserter,
        DocumentRenderer $documentRenderer,
        DocumentRevisionCreator $documentRevisionCreator,
        DocumentValidator $documentValidator,
        IdGenerator $idGenerator,
        KnowledgebaseOwner $knowledgebaseOwner,
        KnowledgebaseRepository $knowledgebaseRepository,
        KnowledgebaseRoute $knowledgebaseRoute,
        KnowledgebaseRouteRepository $knowledgebaseRouteRepository,
        TopicRepository $topicRepository,
        UrlIdGenerator $urlIdGenerator,
        UrlSlugGenerator $urlSlugGenerator
    ) {
        $this->connection = $connection;
        $this->documentDescriptionDeducer = $documentDescriptionDeducer;
        $this->documentInserter = $documentInserter;
        $this->documentRenderer = $documentRenderer;
        $this->documentRevisionCreator = $documentRevisionCreator;
        $this->documentValidator = $documentValidator;
        $this->idGenerator = $idGenerator;
        $this->knowledgebaseOwner = $knowledgebaseOwner;
        $this->knowledgebaseRepository = $knowledgebaseRepository;
        $this->knowledgebaseRoute = $knowledgebaseRoute;
        $this->knowledgebaseRouteRepository = $knowledgebaseRouteRepository;
        $this->topicRepository = $topicRepository;
        $this->urlIdGenerator = $urlIdGenerator;
        $this->urlSlugGenerator = $urlSlugGenerator;
    }

    public function create(PersonModel $person, array $parameters): array
    {
        $organizationId = $person->getOrganizationId();
        $knowledgebase = $this->getKnowledgebase($parameters, $organizationId);
        $topic = $this->getTopic($parameters['topic_id'] ?? null, $knowledgebase, $organizationId);
        $this->documentValidator->validate($parameters, $knowledgebase, $topic, true);

        $id = $this->idGenerator->generate();
        $urlSlug = $this->urlSlugGenerator->generateFromString($parameters['name']);
        $urlId = $this->urlIdGenerator->generate();

        $deducedDescription = null;
        if (isset($parameters['content']) && is_array($parameters['content'])) {
            $deducedDescription = $this->documentDescriptionDeducer->deduce($parameters['content']);
        }

        $content = null;
        if (isset($parameters['content']) && is_array($parameters['content'])) {
            $content = json_encode($parameters['content'], JSON_THROW_ON_ERROR);
        }

        $contentPlain = null;
        if (null !== $content) {
            $rendererResult = $this->documentRenderer->render($content, 'text');
            $contentPlain = $rendererResult->getContent();
        }

        $this->connection->beginTransaction();
        try {
            $result = $this->documentInserter->insert(
                $id,
                $parameters['name'],
                $urlSlug,
                $urlId,
                $parameters['knowledgebase_id'],
                $person->getOrganizationId(),
                $person->getId(),
                $parameters['description'] ?? null,
                $deducedDescription,
                $content,
                $contentPlain,
                $parameters['topic_id'] ?? null
            );
            $model = DocumentModel::createFromArray($result);

            $routePrefix = $this->getRoutePrefix($organizationId, $parameters['knowledgebase_id'], $topic);
            $route = $this->knowledgebaseRoute->create(
                $model,
                $routePrefix . $model->getUrlSlug(),
                true,
                true
            );
            $this->documentRevisionCreator->create($model);

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

        $knowledgebaseOwnerModel = $this->knowledgebaseOwner->get($knowledgebase);

        return [$route, $knowledgebaseOwnerModel];
    }

    private function getKnowledgebase(array $parameters, string $organizationId): ?KnowledgebaseModel
    {
        if (!isset($parameters['knowledgebase_id'])) {
            return null;
        }

        $result = $this->knowledgebaseRepository->findById($parameters['knowledgebase_id'], $organizationId);
        if (null === $result) {
            return null;
        }

        return KnowledgebaseModel::createFromArray($result);
    }

    private function getTopic(
        ?string $topicId,
        ?KnowledgebaseModel $knowledgebase,
        string $organizationId
    ): ?TopicModel {
        if (null === $topicId) {
            return null;
        }

        if (!$knowledgebase instanceof KnowledgebaseModel) {
            return null;
        }

        $result = $this->topicRepository->findByIdInKnowledgebase(
            $topicId,
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
                'Cannot create document in topic %s because it is missing a canonical knowledgebase route',
                $topic->getId()
            ));
        }

        return $result['route'] . '/';
    }
}
