<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Knowledgebase;

use Hipper\FrontEnd\App\Controller\Document\DocumentController;
use Hipper\FrontEnd\App\Controller\Topic\TopicController;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

class DocumentOrTopicControllerRouter
{
    private DocumentController $documentController;
    private TopicController $topicController;

    public function __construct(
        DocumentController $documentController,
        TopicController $topicController
    ) {
        $this->documentController = $documentController;
        $this->topicController = $topicController;
    }

    public function route(Request $request)
    {
        $entityType = $request->attributes->get('entity_type');
        $action = $request->attributes->get('action');

        switch ($entityType) {
            case 'document':
                return $this->documentController->$action($request);
            case 'topic':
                return $this->topicController->$action($request);
            default:
                throw new RuntimeException('Unsupported knowledgebase route entity type');
        }
    }
}
