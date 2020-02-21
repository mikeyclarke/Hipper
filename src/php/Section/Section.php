<?php
declare(strict_types=1);

namespace Hipper\Section;

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
    private SectionUpdater $sectionUpdater;
    private SectionValidator $sectionValidator;
    private UpdateSectionDescendantRoutes $updateSectionDescendantRoutes;
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
        SectionUpdater $sectionUpdater,
        SectionValidator $sectionValidator,
        UpdateSectionDescendantRoutes $updateSectionDescendantRoutes,
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
        $this->sectionUpdater = $sectionUpdater;
        $this->sectionValidator = $sectionValidator;
        $this->updateSectionDescendantRoutes = $updateSectionDescendantRoutes;
        $this->urlIdGenerator = $urlIdGenerator;
        $this->urlSlugGenerator = $urlSlugGenerator;
    }

    public function create(PersonModel $person, array $parameters): array
    {
        $organizationId = $person->getOrganizationId();
        $knowledgebase = $this->getKnowledgebase($parameters, $organizationId);
        $parentSection = $this->getParentSection(
            $parameters['parent_section_id'] ?? null,
            $knowledgebase,
            $organizationId
        );
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

    public function update(PersonModel $person, SectionModel $section, array $parameters): array
    {
        if ($person->getOrganizationId() !== $section->getOrganizationId()) {
            throw new ResourceIsForeignToOrganizationException(sprintf(
                'Section with ID “%d” does not belong to current user’s organization',
                $section->getId()
            ));
        }

        $organizationId = $section->getOrganizationId();
        $knowledgebaseId = $section->getKnowledgebaseId();

        $result = $this->knowledgebaseRepository->findById($knowledgebaseId, $organizationId);
        if (null === $result) {
            throw new KnowledgebaseNotFoundException;
        }
        $knowledgebase = KnowledgebaseModel::createFromArray($result);
        $knowledgebaseOwnerModel = $this->knowledgebaseOwner->get($knowledgebase);
        $parentSection = $this->getParentSection(
            $parameters['parent_section_id'] ?? null,
            $knowledgebase,
            $organizationId
        );

        $this->sectionValidator->validate($parameters, null, $parentSection);

        $propertiesToUpdate = [];

        if (isset($parameters['name']) && $parameters['name'] !== $section->getName()) {
            $propertiesToUpdate['name'] = $parameters['name'];

            $urlSlug = $this->urlSlugGenerator->generateFromString($parameters['name']);
            if ($urlSlug !== $section->getUrlSlug()) {
                $propertiesToUpdate['url_slug'] = $urlSlug;
            }
        }

        if (array_key_exists('description', $parameters) &&
            $parameters['description'] !== $section->getDescription()
        ) {
            $propertiesToUpdate['description'] = $parameters['description'];
        }

        if (array_key_exists('parent_section_id', $parameters) &&
            $parameters['parent_section_id'] !== $section->getParentSectionId()
        ) {
            $propertiesToUpdate['parent_section_id'] = $parameters['parent_section_id'];
        }

        $routeHasChanged = isset($propertiesToUpdate['url_slug']) ||
            array_key_exists('parent_section_id', $propertiesToUpdate);

        $route = null;
        if (!$routeHasChanged) {
            $routeResult = $this->knowledgebaseRouteRepository->findCanonicalRouteForSection(
                $organizationId,
                $knowledgebaseId,
                $section->getId()
            );
            if (null === $routeResult) {
                throw new MissingRouteException('Section has no canonical route');
            }

            $route = KnowledgebaseRouteModel::createFromArray($routeResult);
        }

        if (empty($propertiesToUpdate)) {
            return [$section, $route, $knowledgebaseOwnerModel];
        }

        if (!array_key_exists('parent_section_id', $propertiesToUpdate) && $routeHasChanged) {
            $parentSection = $this->getParentSection($section->getParentSectionId(), $knowledgebase, $organizationId);
        }

        $this->connection->beginTransaction();
        try {
            $result = $this->sectionUpdater->update(
                $section->getId(),
                $propertiesToUpdate
            );
            $section->updateFromArray($result);

            if ($routeHasChanged) {
                $routePrefix = $this->getRoutePrefix($organizationId, $knowledgebaseId, $parentSection);
                $route = $this->knowledgebaseRoute->create(
                    $section,
                    $routePrefix . $section->getUrlSlug(),
                    true
                );
            }

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

        if ($routeHasChanged) {
            $this->updateSectionDescendantRoutes->update($section, $route);
        }

        return [$section, $route, $knowledgebaseOwnerModel];
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
        ?string $parentSectionId,
        ?KnowledgebaseModel $knowledgebase,
        string $organizationId
    ): ?SectionModel {
        if (null === $parentSectionId) {
            return null;
        }

        if (!$knowledgebase instanceof KnowledgebaseModel) {
            return null;
        }

        $result = $this->sectionRepository->findByIdInKnowledgebase(
            $parentSectionId,
            $knowledgebase->getId(),
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
