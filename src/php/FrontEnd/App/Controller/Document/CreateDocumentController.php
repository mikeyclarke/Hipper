<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Document;

use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseEntityException;
use Hipper\Knowledgebase\KnowledgebaseBreadcrumbs;
use Hipper\Knowledgebase\KnowledgebaseOwnerModelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class CreateDocumentController
{
    const DEFAULT_DOC_TITLE = 'Untitled doc';

    private $knowledgebaseBreadcrumbs;
    private $twig;
    private $documentAllowedMarks;
    private $documentAllowedNodes;

    public function __construct(
        KnowledgebaseBreadcrumbs $knowledgebaseBreadcrumbs,
        Twig $twig,
        array $documentAllowedMarks,
        array $documentAllowedNodes
    ) {
        $this->knowledgebaseBreadcrumbs = $knowledgebaseBreadcrumbs;
        $this->twig = $twig;
        $this->documentAllowedMarks = $documentAllowedMarks;
        $this->documentAllowedNodes = $documentAllowedNodes;
    }

    public function getAction(Request $request): Response
    {
        $organization = $request->attributes->get('organization');
        $knowledgebaseType = $request->attributes->get('knowledgebase_type');

        $context = [
            'allowed_marks' => $this->documentAllowedMarks,
            'allowed_nodes' => $this->documentAllowedNodes,
            'htmlClassList' => ['l-document-editor'],
        ];

        switch ($knowledgebaseType) {
            case 'team':
                $knowledgebaseOwner = $request->attributes->get('team');
                $context = array_merge($context, $this->createTeamContext($request, $knowledgebaseOwner));
                break;
            case 'project':
                $knowledgebaseOwner = $request->attributes->get('project');
                $context = array_merge($context, $this->createProjectContext($request, $knowledgebaseOwner));
                break;
            default:
                throw new UnsupportedKnowledgebaseEntityException;
        }

        $breadcrumbs = $this->knowledgebaseBreadcrumbs->get(
            $organization,
            $knowledgebaseOwner,
            self::DEFAULT_DOC_TITLE,
            $context['topic_id']
        );

        $backLink = $breadcrumbs[count($breadcrumbs) - 2]['pathname'];

        $context['back_link'] = $backLink;
        $context['breadcrumbs'] = $breadcrumbs;

        return new Response(
            $this->twig->render('document/create_document.twig', $context)
        );
    }

    private function createTeamContext(Request $request, KnowledgebaseOwnerModelInterface $team): array
    {
        return [
            'html_title' => sprintf('New doc – %s', $team->getName()),
            'knowledgebase_id' => $team->getKnowledgebaseId(),
            'topic_id' => $request->query->get('in', null),
            'team' => $team,
        ];
    }

    private function createProjectContext(Request $request, KnowledgebaseOwnerModelInterface $project): array
    {
        return [
            'html_title' => sprintf('New doc – %s', $project->getName()),
            'knowledgebase_id' => $project->getKnowledgebaseId(),
            'topic_id' => $request->query->get('in', null),
            'project' => $project,
        ];
    }
}
