<?php
declare(strict_types=1);

namespace Hipper\Document;

use Doctrine\DBAL\Connection;
use Hipper\Document\Exception\MissingRouteException;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Knowledgebase\KnowledgebaseModel;
use Hipper\Knowledgebase\KnowledgebaseOwner;
use Hipper\Knowledgebase\KnowledgebaseRepository;
use Hipper\Knowledgebase\KnowledgebaseRoute;
use Hipper\Knowledgebase\KnowledgebaseRouteRepository;
use Hipper\Person\PersonModel;
use Hipper\Section\SectionModel;
use Hipper\Section\SectionRepository;
use Hipper\Url\UrlIdGenerator;
use Hipper\Url\UrlSlugGenerator;
use JSON_THROW_ON_ERROR;

class Document
{
    private $connection;
    private $documentDescriptionDeducer;
    private $documentInserter;
    private $documentRevision;
    private $documentValidator;
    private $idGenerator;
    private $knowledgebaseOwner;
    private $knowledgebaseRepository;
    private $knowledgebaseRoute;
    private $knowledgebaseRouteRepository;
    private $sectionRepository;
    private $urlIdGenerator;
    private $urlSlugGenerator;

    public function __construct(
        Connection $connection,
        DocumentDescriptionDeducer $documentDescriptionDeducer,
        DocumentInserter $documentInserter,
        DocumentRevision $documentRevision,
        DocumentValidator $documentValidator,
        IdGenerator $idGenerator,
        KnowledgebaseOwner $knowledgebaseOwner,
        KnowledgebaseRepository $knowledgebaseRepository,
        KnowledgebaseRoute $knowledgebaseRoute,
        KnowledgebaseRouteRepository $knowledgebaseRouteRepository,
        SectionRepository $sectionRepository,
        UrlIdGenerator $urlIdGenerator,
        UrlSlugGenerator $urlSlugGenerator
    ) {
        $this->connection = $connection;
        $this->documentDescriptionDeducer = $documentDescriptionDeducer;
        $this->documentInserter = $documentInserter;
        $this->documentRevision = $documentRevision;
        $this->documentValidator = $documentValidator;
        $this->idGenerator = $idGenerator;
        $this->knowledgebaseOwner = $knowledgebaseOwner;
        $this->knowledgebaseRepository = $knowledgebaseRepository;
        $this->knowledgebaseRoute = $knowledgebaseRoute;
        $this->knowledgebaseRouteRepository = $knowledgebaseRouteRepository;
        $this->sectionRepository = $sectionRepository;
        $this->urlIdGenerator = $urlIdGenerator;
        $this->urlSlugGenerator = $urlSlugGenerator;
    }

    public function create(PersonModel $person, array $parameters): array
    {
        $organizationId = $person->getOrganizationId();
        $knowledgebase = $this->getKnowledgebase($parameters, $organizationId);
        $section = $this->getSection($parameters, $knowledgebase, $organizationId);
        $this->documentValidator->validate($parameters, $knowledgebase, $section, true);

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
                $parameters['section_id'] ?? null
            );
            $model = DocumentModel::createFromArray($result);

            $routePrefix = $this->getRoutePrefix($organizationId, $parameters['knowledgebase_id'], $section);
            $route = $this->knowledgebaseRoute->create(
                $model,
                $routePrefix . $model->getUrlSlug(),
                true,
                true
            );
            $this->documentRevision->create($model);

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

        $knowledgebaseOwnerModel = $this->knowledgebaseOwner->get($knowledgebase);

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

    private function getSection(
        array $parameters,
        ?KnowledgebaseModel $knowledgebase,
        string $organizationId
    ): ?SectionModel {
        if (!isset($parameters['section_id']) || null === $parameters['section_id']) {
            return null;
        }

        if (!$knowledgebase instanceof KnowledgebaseModel) {
            return null;
        }

        $result = $this->sectionRepository->findById(
            $parameters['section_id'],
            $parameters['knowledgebase_id'],
            $organizationId
        );
        if (null === $result) {
            return null;
        }

        return SectionModel::createFromArray($result);
    }

    private function getRoutePrefix(string $organizationId, string $knowledgebaseId, ?SectionModel $section): string
    {
        if (null === $section) {
            return '';
        }

        $result = $this->knowledgebaseRouteRepository->findCanonicalRouteForSection(
            $organizationId,
            $knowledgebaseId,
            $section->getId()
        );

        if (null === $result) {
            throw new MissingRouteException(sprintf(
                'Cannot create document in section %s because it is missing a canonical knowledgebase route',
                $section->getId()
            ));
        }

        return $result['route'] . '/';
    }
}
