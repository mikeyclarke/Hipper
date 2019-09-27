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
        $personIsInProject = $request->attributes->get('personIsInProject');

        $context = [
            'html_title' => $project->getName(),
            'project' => $project,
            'personIsInProject' => $personIsInProject,
        ];

        return new Response(
            $this->twig->render('project/project_index.twig', $context)
        );
    }
}
