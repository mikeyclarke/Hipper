<?php
declare(strict_types=1);

namespace Hipper\App\Document;

use Hipper\Document\Document;
use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseEntityException;
use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class CreateDocumentController
{
    private $document;
    private $knowledgebaseRouteUrlGenerator;
    private $twig;
    private $documentAllowedMarks;
    private $documentAllowedNodes;

    public function __construct(
        Document $document,
        KnowledgebaseRouteUrlGenerator $knowledgebaseRouteUrlGenerator,
        Twig $twig,
        array $documentAllowedMarks,
        array $documentAllowedNodes
    ) {
        $this->document = $document;
        $this->knowledgebaseRouteUrlGenerator = $knowledgebaseRouteUrlGenerator;
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

    public function postAction(Request $request): JsonResponse
    {
        $person = $request->attributes->get('person');

        try {
            list($model, $route, $knowledgebaseOwner) = $this->document->create($person, $request->request->all());
        } catch (ValidationException $e) {
            return new JsonResponse(
                [
                    'name' => $e->getName(),
                    'message' => $e->getMessage(),
                    'violations' => $e->getViolations(),
                ],
                400
            );
        }

        $url = $this->knowledgebaseRouteUrlGenerator->generate($knowledgebaseOwner, $route);
        return new JsonResponse(['doc_url' => $url], 201);
    }

    private function createTeamContext(Request $request): array
    {
        $team = $request->attributes->get('team');
        return [
            'html_title' => sprintf('New doc – %s', $team->getName()),
            'knowledgebase_id' => $team->getKnowledgebaseId(),
            'team' => $team,
        ];
    }

    private function createProjectContext(Request $request): array
    {
        $project = $request->attributes->get('project');
        return [
            'html_title' => sprintf('New doc – %s', $project->getName()),
            'knowledgebase_id' => $project->getKnowledgebaseId(),
            'project' => $project,
        ];
    }
}
