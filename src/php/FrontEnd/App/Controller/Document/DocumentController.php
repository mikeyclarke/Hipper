<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Document;

use Hipper\Document\DocumentRevisionRepository;
use Hipper\Document\DocumentRenderer;
use Hipper\Knowledgebase\KnowledgebaseBreadcrumbs;
use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as Twig;

class DocumentController
{
    private DocumentRenderer $documentRenderer;
    private DocumentRevisionRepository $documentRevisionRepository;
    private KnowledgebaseBreadcrumbs $knowledgebaseBreadcrumbs;
    private KnowledgebaseRouteUrlGenerator $knowledgebaseRouteUrlGenerator;
    private Twig $twig;

    public function __construct(
        DocumentRenderer $documentRenderer,
        DocumentRevisionRepository $documentRevisionRepository,
        KnowledgebaseBreadcrumbs $knowledgebaseBreadcrumbs,
        KnowledgebaseRouteUrlGenerator $knowledgebaseRouteUrlGenerator,
        Twig $twig
    ) {
        $this->documentRenderer = $documentRenderer;
        $this->documentRevisionRepository = $documentRevisionRepository;
        $this->knowledgebaseBreadcrumbs = $knowledgebaseBreadcrumbs;
        $this->knowledgebaseRouteUrlGenerator = $knowledgebaseRouteUrlGenerator;
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $organization = $request->attributes->get('organization');
        $document = $request->attributes->get('document');
        $knowledgebaseType = $request->attributes->get('knowledgebase_type');
        $knowledgebaseOwner = $request->attributes->get($knowledgebaseType);
        $route = $request->attributes->get('knowledgebase_route');

        $breadcrumbs = $this->knowledgebaseBreadcrumbs->get(
            $organization,
            $knowledgebaseOwner,
            $document->getName(),
            $document->getTopicId()
        );

        $backLink = $breadcrumbs[count($breadcrumbs) - 2]['pathname'];

        $history = $this->documentRevisionRepository->getHistoryForDocument(
            $document->getId(),
            $document->getKnowledgebaseId(),
            $document->getOrganizationId()
        );

        $rendererResult = $this->documentRenderer->render($document->getContent(), 'html', $request->getHost(), true);
        $editUrl = $this->knowledgebaseRouteUrlGenerator->generate($organization, $knowledgebaseOwner, $route, 'edit');
        $exportUrl = $this->knowledgebaseRouteUrlGenerator->generate(
            $organization,
            $knowledgebaseOwner,
            $route,
            'export'
        );
        $viewUrl = $this->knowledgebaseRouteUrlGenerator->generate(
            $organization,
            $knowledgebaseOwner,
            $route,
            'show',
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $context = [
            'back_link' => $backLink,
            'breadcrumbs' => $breadcrumbs,
            'document' => $document,
            'document_html' => $rendererResult->getContent(),
            'document_outline' => $rendererResult->getOutline(),
            'document_history' => $history,
            'edit_url' => $editUrl,
            'export_url' => $exportUrl,
            'html_title' => sprintf('%s – %s', $document->getName(), $knowledgebaseOwner->getName()),
            'htmlClassList' => ['l-document-editor'],
            'view_url' => $viewUrl,
        ];

        return new Response($this->twig->render('document/view.twig', $context));
    }
}
