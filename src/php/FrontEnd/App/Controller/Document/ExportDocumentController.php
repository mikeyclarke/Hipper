<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Document;

use Hipper\Document\DocumentExporter;
use Hipper\Document\DocumentModel;
use Hipper\Document\DocumentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExportDocumentController
{
    private DocumentExporter $documentExporter;
    private DocumentRepository $documentRepository;

    public function __construct(
        DocumentExporter $documentExporter,
        DocumentRepository $documentRepository
    ) {
        $this->documentExporter = $documentExporter;
        $this->documentRepository = $documentRepository;
    }

    public function getAction(Request $request): Response
    {
        $organization = $request->attributes->get('organization');
        $documentId = $request->attributes->get('document_id');

        $result = $this->documentRepository->findById($documentId, $organization->getId());
        if (null === $result) {
            throw new NotFoundHttpException;
        }

        $document = DocumentModel::createFromArray($result);
        list($content, $fileName) = $this->documentExporter->export($document, $request->getHttpHost());

        return new Response(
            $content,
            200,
            [
                'Content-Disposition' => sprintf('attachment; filename="%s"', $fileName),
                'Content-Type' => 'text/plain',
            ]
        );
    }
}
