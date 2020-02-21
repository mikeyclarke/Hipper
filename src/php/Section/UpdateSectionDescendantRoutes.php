<?php
declare(strict_types=1);

namespace Hipper\Section;

use Doctrine\DBAL\Connection;
use Hipper\Document\DocumentModel;
use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseContentTypeException;
use Hipper\Knowledgebase\KnowledgebaseRoute;
use Hipper\Knowledgebase\KnowledgebaseRouteModel;
use RuntimeException;

class UpdateSectionDescendantRoutes
{
    private Connection $connection;
    private KnowledgebaseRoute $knowledgebaseRoute;
    private SectionRepository $sectionRepository;

    public function __construct(
        Connection $connection,
        KnowledgebaseRoute $knowledgebaseRoute,
        SectionRepository $sectionRepository
    ) {
        $this->connection = $connection;
        $this->knowledgebaseRoute = $knowledgebaseRoute;
        $this->sectionRepository = $sectionRepository;
    }

    public function update(SectionModel $section, KnowledgebaseRouteModel $route): void
    {
        $descendants = $this->getSectionDescendants($section);
        $sectionRoutes = [];

        $sectionRoutes[$section->getId()] = $route;

        foreach ($descendants as $descendant) {
            if ($descendant['id'] === $section->getId()) {
                continue;
            }

            if (!isset($sectionRoutes[$descendant['parent_section_id']])) {
                throw new RuntimeException('Parent section not found');
            }

            $routePrefix = $sectionRoutes[$descendant['parent_section_id']]->getRoute() . '/';

            switch ($descendant['type']) {
                case 'document':
                    $descendant['section_id'] = $descendant['parent_section_id'];
                    $model = DocumentModel::createFromArray($descendant);
                    break;
                case 'section':
                    $model = SectionModel::createFromArray($descendant);
                    break;
                default:
                    throw new UnsupportedKnowledgebaseContentTypeException;
            }

            $this->connection->beginTransaction();
            try {
                $route = $this->knowledgebaseRoute->create(
                    $model,
                    $routePrefix . $model->getUrlSlug(),
                    true
                );

                $this->connection->commit();
            } catch (\Exception $e) {
                $this->connection->rollBack();
                throw $e;
            }

            if ($model instanceof SectionModel) {
                $sectionRoutes[$model->getId()] = $route;
            }
        }
    }

    private function getSectionDescendants(SectionModel $section): array
    {
        return $this->sectionRepository->getSectionAndDescendants(
            $section->getId(),
            $section->getKnowledgebaseId(),
            $section->getOrganizationId()
        );
    }
}
