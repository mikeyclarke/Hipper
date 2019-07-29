<?php
declare(strict_types=1);

namespace Lithos\Knowledgebase;

use Lithos\Document\DocumentModel;
use Lithos\IdGenerator\IdGenerator;

class KnowledgebaseRoute
{
    private $idGenerator;
    private $knowledgebaseRouteInserter;
    private $knowledgebaseRouteUpdater;

    public function __construct(
        IdGenerator $idGenerator,
        KnowledgebaseRouteInserter $knowledgebaseRouteInserter,
        KnowledgebaseRouteUpdater $knowledgebaseRouteUpdater
    ) {
        $this->idGenerator = $idGenerator;
        $this->knowledgebaseRouteInserter = $knowledgebaseRouteInserter;
        $this->knowledgebaseRouteUpdater = $knowledgebaseRouteUpdater;
    }

    public function createForDocument(
        DocumentModel $document,
        string $route,
        bool $isCanonicalRoute = true,
        bool $isNewDocument = false
    ): void {
        $id = $this->idGenerator->generate();

        $result = $this->knowledgebaseRouteInserter->insert(
            $id,
            $document->getUrlId(),
            $route,
            'document',
            $document->getOrganizationId(),
            $document->getKnowledgebaseId(),
            null,
            $document->getId(),
            $isCanonicalRoute
        );

        if ($isCanonicalRoute && !$isNewDocument) {
            $this->knowledgebaseRouteUpdater->updatePreviousCanonicalRoutes(
                $result['id'],
                $result['url_id'],
                $result['knowledgebase_id'],
                $result['organization_id']
            );
        }
    }
}
