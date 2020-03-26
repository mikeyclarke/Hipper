<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Document;

use Hipper\Document\DocumentModel;
use Hipper\Document\DocumentRenderer;
use Hipper\Document\DocumentRepository;
use Hipper\Knowledgebase\KnowledgebaseBreadcrumbs;
use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment as Twig;

class EditDocumentController
{
    private DocumentRenderer $documentRenderer;
    private DocumentRepository $documentRepository;
    private KnowledgebaseBreadcrumbs $knowledgebaseBreadcrumbs;
    private KnowledgebaseRouteUrlGenerator $knowledgebaseRouteUrlGenerator;
    private Twig $twig;
    private array $documentAllowedMarks;
    private array $documentAllowedNodes;

    public function __construct(
        DocumentRenderer $documentRenderer,
        DocumentRepository $documentRepository,
        KnowledgebaseBreadcrumbs $knowledgebaseBreadcrumbs,
        KnowledgebaseRouteUrlGenerator $knowledgebaseRouteUrlGenerator,
        Twig $twig,
        array $documentAllowedMarks,
        array $documentAllowedNodes
    ) {
        $this->documentRenderer = $documentRenderer;
        $this->documentRepository = $documentRepository;
        $this->knowledgebaseBreadcrumbs = $knowledgebaseBreadcrumbs;
        $this->knowledgebaseRouteUrlGenerator = $knowledgebaseRouteUrlGenerator;
        $this->twig = $twig;
        $this->documentAllowedMarks = $documentAllowedMarks;
        $this->documentAllowedNodes = $documentAllowedNodes;
    }

    public function getAction(Request $request): Response
    {
        if (!$request->attributes->has('document_id')) {
            throw new NotFoundHttpException;
        }

        $organization = $request->attributes->get('organization');
        $documentId = $request->attributes->get('document_id');
        $knowledgebaseType = $request->attributes->get('knowledgebase_type');
        $knowledgebaseOwner = $request->attributes->get($knowledgebaseType);
        $route = $request->attributes->get('knowledgebase_route');

        $result = $this->documentRepository->findById($documentId, $organization->getId());
        if (null === $result) {
            throw new NotFoundHttpException;
        }

        $document = DocumentModel::createFromArray($result);

        $breadcrumbs = $this->knowledgebaseBreadcrumbs->get(
            $organization,
            $knowledgebaseOwner,
            $document->getName(),
            $document->getTopicId()
        );

        $backLink = $breadcrumbs[count($breadcrumbs) - 2]['pathname'];

        $rendererResult = $this->documentRenderer->render($document->getContent(), 'html', $request->getHost());
        $viewUrl = $this->knowledgebaseRouteUrlGenerator->generate($organization, $knowledgebaseOwner, $route);

        $context = [
            'allowed_marks' => $this->documentAllowedMarks,
            'allowed_nodes' => $this->documentAllowedNodes,
            'back_link' => $backLink,
            'breadcrumbs' => $breadcrumbs,
            'document' => $document,
            'document_html' => $rendererResult->getContent(),
            'html_title' => sprintf('Edit %s – %s', $document->getName(), $knowledgebaseOwner->getName()),
            'htmlClassList' => ['l-document-editor'],
            'view_url' => $viewUrl,
        ];

        return new Response($this->twig->render('document/edit.twig', $context));
    }
}
