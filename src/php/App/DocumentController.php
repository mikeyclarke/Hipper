<?php
declare(strict_types=1);

namespace Hipper\App;

use Hipper\Document\DocumentModelMapper;
use Hipper\Document\DocumentRepository;
use Hipper\Document\Renderer\HtmlRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig_Environment;

class DocumentController
{
    private $documentModelMapper;
    private $documentRepository;
    private $htmlRenderer;
    private $twig;

    public function __construct(
        DocumentModelMapper $documentModelMapper,
        DocumentRepository $documentRepository,
        HtmlRenderer $htmlRenderer,
        Twig_Environment $twig
    ) {
        $this->documentModelMapper = $documentModelMapper;
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

        $document = $this->documentModelMapper->createFromArray($result);

        $context = [
            'document' => $document,
            'document_html' => $this->htmlRenderer->render($document->getContent(), $request->getHost()),
            'html_title' => $document->getName(),
        ];

        return new Response($this->twig->render('document/document.twig', $context));
    }
}