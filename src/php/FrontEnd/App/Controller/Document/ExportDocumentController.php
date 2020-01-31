<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Document;

use Hipper\Document\DocumentModel;
use Hipper\Document\DocumentRenderer;
use Hipper\Document\DocumentRepository;
use Hipper\File\FileNameGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExportDocumentController
{
    private const DEFAULT_FILE_NAME = 'Untitled doc';

    private DocumentRepository $documentRepository;
    private DocumentRenderer $documentRenderer;
    private FileNameGenerator $fileNameGenerator;

    public function __construct(
        DocumentRepository $documentRepository,
        DocumentRenderer $documentRenderer,
        FileNameGenerator $fileNameGenerator
    ) {
        $this->documentRepository = $documentRepository;
        $this->documentRenderer = $documentRenderer;
        $this->fileNameGenerator = $fileNameGenerator;
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

        $rendererResult = $this->documentRenderer->render($document->getContent(), 'markdown', $request->getHost());
        $fileName = $this->fileNameGenerator->generateFromString($document->getName(), 'md', self::DEFAULT_FILE_NAME);

        return new Response(
            $rendererResult->getContent(),
            200,
            [
                'Content-Disposition' => sprintf('attachment; filename="%s"', $fileName),
                'Content-Type' => 'text/plain',
            ]
        );
    }
}
