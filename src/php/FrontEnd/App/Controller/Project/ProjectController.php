<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Project;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class ProjectController
{
    private $twig;

    public function __construct(
        Twig $twig
    ) {
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $project = $request->attributes->get('project');
        $currentUserIsInProject = $request->attributes->get('current_user_is_in_project');

        $context = [
            'html_title' => $project->getName(),
            'project' => $project,
            'current_user_is_in_project' => $currentUserIsInProject,
        ];

        return new Response(
            $this->twig->render('project/project_index.twig', $context)
        );
    }
}
