<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Hipper\Document\DocumentModel;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseContentTypeException;
use Hipper\Knowledgebase\Storage\KnowledgebaseRouteInserter;
use Hipper\Knowledgebase\Storage\KnowledgebaseRouteUpdater;
use Hipper\Topic\TopicModel;

class KnowledgebaseRouteCreator
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

    public function create(
        KnowledgebaseContentModelInterface $content,
        string $route,
        bool $isCanonicalRoute = true,
        bool $isNewContent = false
    ): KnowledgebaseRouteModel {
        $contentType = $this->getContentType($content);
        $id = $this->idGenerator->generate();

        $documentId = null;
        $topicId = null;

        switch ($contentType) {
            case 'document':
                $documentId = $content->getId();
                break;
            case 'topic':
                $topicId = $content->getId();
                break;
        }

        $result = $this->knowledgebaseRouteInserter->insert(
            $id,
            $content->getUrlId(),
            $route,
            $contentType,
            $content->getOrganizationId(),
            $content->getKnowledgebaseId(),
            $topicId,
            $documentId,
            $isCanonicalRoute
        );

        if ($isCanonicalRoute && !$isNewContent) {
            $this->knowledgebaseRouteUpdater->updatePreviousCanonicalRoutes(
                $result['id'],
                $result['url_id'],
                $result['knowledgebase_id'],
                $result['organization_id']
            );
        }

        return KnowledgebaseRouteModel::createFromArray($result);
    }

    private function getContentType(KnowledgebaseContentModelInterface $content): string
    {
        if ($content instanceof DocumentModel) {
            return 'document';
        }

        if ($content instanceof TopicModel) {
            return 'topic';
        }

        throw new UnsupportedKnowledgebaseContentTypeException;
    }
}
