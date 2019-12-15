<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Knowledgebase;

use Hipper\FrontEnd\App\Controller\Document\DocumentController;
use Hipper\FrontEnd\App\Controller\Section\SectionController;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

class DocumentOrSectionControllerRouter
{
    private $documentController;
    private $sectionController;

    public function __construct(
        DocumentController $documentController,
        SectionController $sectionController
    ) {
        $this->documentController = $documentController;
        $this->sectionController = $sectionController;
    }

    public function route(Request $request)
    {
        $entityType = $request->attributes->get('entity_type');
        $action = $request->attributes->get('action');

        switch ($entityType) {
            case 'document':
                return $this->documentController->$action($request);
            case 'section':
                return $this->sectionController->$action($request);
            default:
                throw new RuntimeException('Unsupported knowledgebase route entity type');
        }
    }
}
