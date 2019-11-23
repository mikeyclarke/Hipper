<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Document;

use Hipper\Document\DocumentModel;
use Hipper\Document\DocumentRepository;
use Hipper\Document\DocumentRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment as Twig;

class DocumentController
{
    private $documentRenderer;
    private $documentRepository;
    private $twig;

    public function __construct(
        DocumentRenderer $documentRenderer,
        DocumentRepository $documentRepository,
        Twig $twig
    ) {
        $this->documentRenderer = $documentRenderer;
        $this->documentRepository = $documentRepository;
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $organization = $request->attributes->get('organization');
        $documentId = $request->attributes->get('documentId');

        $result = $this->documentRepository->findById($documentId, $organization->getId());
        if (null === $result) {
            throw new NotFoundHttpException;
        }

        $document = DocumentModel::createFromArray($result);

        $rendererResult = $this->documentRenderer->render($document->getContent(), 'html', $request->getHost(), true);

        $context = [
            'document' => $document,
            'document_html' => $rendererResult->getContent(),
            'document_outline' => $rendererResult->getOutline(),
            'html_title' => $document->getName(),
        ];

        return new Response($this->twig->render('document/document.twig', $context));
    }
}
