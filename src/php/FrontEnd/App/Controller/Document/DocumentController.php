<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Document;

use Hipper\Document\DocumentModel;
use Hipper\Document\DocumentRepository;
use Hipper\Document\DocumentRevisionRepository;
use Hipper\Document\DocumentRenderer;
use Hipper\Knowledgebase\KnowledgebaseBreadcrumbsFormatter;
use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Hipper\Section\SectionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as Twig;

class DocumentController
{
    private $documentRenderer;
    private $documentRepository;
    private $documentRevisionRepository;
    private $knowledgebaseBreadcrumbsFormatter;
    private $knowledgebaseRouteUrlGenerator;
    private $sectionRepository;
    private $twig;

    public function __construct(
        DocumentRenderer $documentRenderer,
        DocumentRepository $documentRepository,
        DocumentRevisionRepository $documentRevisionRepository,
        KnowledgebaseBreadcrumbsFormatter $knowledgebaseBreadcrumbsFormatter,
        KnowledgebaseRouteUrlGenerator $knowledgebaseRouteUrlGenerator,
        SectionRepository $sectionRepository,
        Twig $twig
    ) {
        $this->documentRenderer = $documentRenderer;
        $this->documentRepository = $documentRepository;
        $this->documentRevisionRepository = $documentRevisionRepository;
        $this->knowledgebaseBreadcrumbsFormatter = $knowledgebaseBreadcrumbsFormatter;
        $this->knowledgebaseRouteUrlGenerator = $knowledgebaseRouteUrlGenerator;
        $this->sectionRepository = $sectionRepository;
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
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

        $ancestorSections = [];
        if (null !== $document->getSectionId()) {
            $ancestorSections = $this->sectionRepository->getByIdWithAncestors(
                $document->getSectionId(),
                $document->getKnowledgebaseId(),
                $document->getOrganizationId()
            );
        }

        $breadcrumbs = $this->knowledgebaseBreadcrumbsFormatter->format(
            $organization,
            $knowledgebaseOwner,
            array_reverse($ancestorSections),
            $document->getName()
        );

        $backLink = $breadcrumbs[count($breadcrumbs) - 2]['pathname'];

        $history = $this->documentRevisionRepository->getHistoryForDocument(
            $document->getId(),
            $document->getKnowledgebaseId(),
            $document->getOrganizationId()
        );

        $rendererResult = $this->documentRenderer->render($document->getContent(), 'html', $request->getHost(), true);
        $editUrl = $this->knowledgebaseRouteUrlGenerator->generate($organization, $knowledgebaseOwner, $route, 'edit');
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
            'html_title' => sprintf('%s – %s', $document->getName(), $knowledgebaseOwner->getName()),
            'htmlClassList' => ['l-document-editor'],
            'view_url' => $viewUrl,
        ];

        return new Response($this->twig->render('document/view.twig', $context));
    }
}
