<?php
declare(strict_types=1);

namespace Hipper\App;

use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

class DocumentOrSectionControllerRouter
{
    private $documentController;

    public function __construct(
        DocumentController $documentController
    ) {
        $this->documentController = $documentController;
    }

    public function route(Request $request)
    {
        $entityType = $request->attributes->get('entityType');
        $action = $request->attributes->get('action');

        if ($entityType === 'document') {
            return $this->documentController->$action($request);
        }

        throw new RuntimeException('Unsupported knowledgebase route entity type');
    }
}
