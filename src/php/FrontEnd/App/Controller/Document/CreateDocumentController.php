<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Document;

use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseEntityException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class CreateDocumentController
{
    private $twig;
    private $documentAllowedMarks;
    private $documentAllowedNodes;

    public function __construct(
        Twig $twig,
        array $documentAllowedMarks,
        array $documentAllowedNodes
    ) {
        $this->twig = $twig;
        $this->documentAllowedMarks = $documentAllowedMarks;
        $this->documentAllowedNodes = $documentAllowedNodes;
    }

    public function getAction(Request $request): Response
    {
        $knowledgebaseType = $request->attributes->get('knowledgebase_type');

        $context = [
            'allowed_marks' => $this->documentAllowedMarks,
            'allowed_nodes' => $this->documentAllowedNodes,
            'htmlClassList' => ['l-document-editor'],
        ];

        switch ($knowledgebaseType) {
            case 'team':
                $context = array_merge($context, $this->createTeamContext($request));
                break;
            case 'project':
                $context = array_merge($context, $this->createProjectContext($request));
                break;
            default:
                throw new UnsupportedKnowledgebaseEntityException;
        }

        return new Response(
            $this->twig->render('document/create_document.twig', $context)
        );
    }

    private function createTeamContext(Request $request): array
    {
        $team = $request->attributes->get('team');
        return [
            'html_title' => sprintf('New doc – %s', $team->getName()),
            'knowledgebase_id' => $team->getKnowledgebaseId(),
            'section_id' => $request->query->get('in', null),
            'team' => $team,
        ];
    }

    private function createProjectContext(Request $request): array
    {
        $project = $request->attributes->get('project');
        return [
            'html_title' => sprintf('New doc – %s', $project->getName()),
            'knowledgebase_id' => $project->getKnowledgebaseId(),
            'section_id' => $request->query->get('in', null),
            'project' => $project,
        ];
    }
}
