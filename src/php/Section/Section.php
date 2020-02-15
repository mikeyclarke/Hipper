<?php
declare(strict_types=1);

namespace Hipper\Section;

use Doctrine\DBAL\Connection;
use Hipper\Document\Exception\MissingRouteException;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Knowledgebase\KnowledgebaseModel;
use Hipper\Knowledgebase\KnowledgebaseOwner;
use Hipper\Knowledgebase\KnowledgebaseRepository;
use Hipper\Knowledgebase\KnowledgebaseRoute;
use Hipper\Knowledgebase\KnowledgebaseRouteRepository;
use Hipper\Person\PersonModel;
use Hipper\Url\UrlIdGenerator;
use Hipper\Url\UrlSlugGenerator;

class Section
{
    private Connection $connection;
    private IdGenerator $idGenerator;
    private KnowledgebaseOwner $knowledgebaseOwner;
    private KnowledgebaseRepository $knowledgebaseRepository;
    private KnowledgebaseRoute $knowledgebaseRoute;
    private KnowledgebaseRouteRepository $knowledgebaseRouteRepository;
    private SectionInserter $sectionInserter;
    private SectionRepository $sectionRepository;
    private SectionValidator $sectionValidator;
    private UrlIdGenerator $urlIdGenerator;
    private UrlSlugGenerator $urlSlugGenerator;

    public function __construct(
        Connection $connection,
        IdGenerator $idGenerator,
        KnowledgebaseOwner $knowledgebaseOwner,
        KnowledgebaseRepository $knowledgebaseRepository,
        KnowledgebaseRoute $knowledgebaseRoute,
        KnowledgebaseRouteRepository $knowledgebaseRouteRepository,
        SectionInserter $sectionInserter,
        SectionRepository $sectionRepository,
        SectionValidator $sectionValidator,
        UrlIdGenerator $urlIdGenerator,
        UrlSlugGenerator $urlSlugGenerator
    ) {
        $this->connection = $connection;
        $this->idGenerator = $idGenerator;
        $this->knowledgebaseOwner = $knowledgebaseOwner;
        $this->knowledgebaseRepository = $knowledgebaseRepository;
        $this->knowledgebaseRoute = $knowledgebaseRoute;
        $this->knowledgebaseRouteRepository = $knowledgebaseRouteRepository;
        $this->sectionInserter = $sectionInserter;
        $this->sectionRepository = $sectionRepository;
        $this->sectionValidator = $sectionValidator;
        $this->urlIdGenerator = $urlIdGenerator;
        $this->urlSlugGenerator = $urlSlugGenerator;
    }

    public function create(PersonModel $person, array $parameters): array
    {
        $organizationId = $person->getOrganizationId();
        $knowledgebase = $this->getKnowledgebase($parameters, $organizationId);
        $parentSection = $this->getParentSection($parameters, $knowledgebase, $organizationId);
        $this->sectionValidator->validate($parameters, $knowledgebase, $parentSection, true);

        $id = $this->idGenerator->generate();
        $urlSlug = $this->urlSlugGenerator->generateFromString($parameters['name']);
        $urlId = $this->urlIdGenerator->generate();

        $this->connection->beginTransaction();
        try {
            $result = $this->sectionInserter->insert(
                $id,
                $parameters['name'],
                $urlSlug,
                $urlId,
                $parameters['knowledgebase_id'],
                $person->getOrganizationId(),
                $parameters['description'] ?? null,
                $parameters['parent_section_id'] ?? null
            );

            $model = SectionModel::createFromArray($result);

            $routePrefix = $this->getRoutePrefix($organizationId, $parameters['knowledgebase_id'], $parentSection);
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

    private function getParentSection(
        array $parameters,
        ?KnowledgebaseModel $knowledgebase,
        string $organizationId
    ): ?SectionModel {
        if (!isset($parameters['parent_section_id'])) {
            return null;
        }

        if (!$knowledgebase instanceof KnowledgebaseModel) {
            return null;
        }

        $result = $this->sectionRepository->findById(
            $parameters['parent_section_id'],
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
                'Cannot create section because parent section %s is missing a canonical knowledgebase route',
                $section->getId()
            ));
        }

        return $result['route'] . '/';
    }
}
