<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Document;

use Hipper\Document\DocumentModel;
use Hipper\Document\DocumentRepository;
use Hipper\Document\Renderer\HtmlRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment as Twig;

class DocumentController
{
    private $documentRepository;
    private $htmlRenderer;
    private $twig;

    public function __construct(
        DocumentRepository $documentRepository,
        HtmlRenderer $htmlRenderer,
        Twig $twig
    ) {
        $this->documentRepository = $documentRepository;
        $this->htmlRenderer = $htmlRenderer;
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

        $context = [
            'document' => $document,
            'document_html' => $this->htmlRenderer->render($document->getContent(), $request->getHost()),
            'html_title' => $document->getName(),
        ];

        return new Response($this->twig->render('document/document.twig', $context));
    }
}
