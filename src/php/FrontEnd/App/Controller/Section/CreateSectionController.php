<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Section;

use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseEntityException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class CreateSectionController
{
    private $twig;

    public function __construct(
        Twig $twig
    ) {
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

    private function getProjectGetActionContext(Request $request): array
    {
        $project = $request->attributes->get('project');
        $currentUserIsInProject = $request->attributes->get('current_user_is_in_project');

        return [
            'knowledgebase_id' => $project->getKnowledgebaseId(),
            'current_user_is_in_project' => $currentUserIsInProject,
            'parent_section_id' => $request->query->get('in', null),
            'project' => $project,
        ];
    }

    private function getTeamGetActionContext(Request $request): array
    {
        $team = $request->attributes->get('team');
        $currentUserIsInTeam = $request->attributes->get('current_user_is_in_team');

        return [
            'knowledgebase_id' => $team->getKnowledgebaseId(),
            'current_user_is_in_team' => $currentUserIsInTeam,
            'parent_section_id' => $request->query->get('in', null),
            'team' => $team,
        ];
    }
}
