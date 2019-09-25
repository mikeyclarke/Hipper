<?php
declare(strict_types=1);

namespace Hipper\App\Section;

use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseEntityException;
use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Hipper\Section\Section;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class CreateSectionController
{
    private $knowledgebaseRouteUrlGenerator;
    private $section;
    private $twig;

    public function __construct(
        KnowledgebaseRouteUrlGenerator $knowledgebaseRouteUrlGenerator,
        Section $section,
        Twig $twig
    ) {
        $this->knowledgebaseRouteUrlGenerator = $knowledgebaseRouteUrlGenerator;
        $this->section = $section;
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $knowledgebaseType = $request->attributes->get('knowledgebase_type');

        switch ($knowledgebaseType) {
            case 'project':
                $twigTemplate = 'project/create_section.twig';
                $context = $this->getProjectGetActionContext($request);
                break;
            case 'team':
                $twigTemplate = 'team/create_section.twig';
                $context = $this->getTeamGetActionContext($request);
                break;
            default:
                throw new UnsupportedKnowledgebaseEntityException;
        }

        return new Response(
            $this->twig->render($twigTemplate, $context)
        );
    }

    public function postAction(Request $request): JsonResponse
    {
        $person = $request->attributes->get('person');

        try {
            list($model, $route, $knowledgebaseOwner) = $this->section->create($person, $request->request->all());
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
        return new JsonResponse(['section_url' => $url], 201);
    }

    private function getProjectGetActionContext(Request $request): array
    {
        $project = $request->attributes->get('project');
        $personIsInProject = $request->attributes->get('personIsInProject');

        return [
            'knowledgebase_id' => $project->getKnowledgebaseId(),
            'personIsInProject' => $personIsInProject,
            'project' => $project,
        ];
    }

    private function getTeamGetActionContext(Request $request): array
    {
        $team = $request->attributes->get('team');
        $personIsInTeam = $request->attributes->get('personIsInTeam');

        return [
            'knowledgebase_id' => $team->getKnowledgebaseId(),
            'personIsInTeam' => $personIsInTeam,
            'team' => $team,
        ];
    }
}
