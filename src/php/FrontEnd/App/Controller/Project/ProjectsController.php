<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Project;

use Hipper\Project\ProjectRepository;
use Hipper\Project\ProjectsListFormatter;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class ProjectsController
{
    private $projectRepository;
    private $projectsListFormatter;
    private $timeZoneFromRequest;
    private $twig;

    public function __construct(
        ProjectRepository $projectRepository,
        ProjectsListFormatter $projectsListFormatter,
        TimeZoneFromRequest $timeZoneFromRequest,
        Twig $twig
    ) {
        $this->projectRepository = $projectRepository;
        $this->projectsListFormatter = $projectsListFormatter;
        $this->timeZoneFromRequest = $timeZoneFromRequest;
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $organization = $request->attributes->get('organization');
        $displayTimeZone = $this->timeZoneFromRequest->get($request);
        $projects = $this->projectRepository->getAll($organization->getId(), 'name', 'ASC');

        $projectsList = $this->projectsListFormatter->format($organization, $projects, $displayTimeZone);

        $context = [
            'html_title' => 'Projects',
            'projects_list' => $projectsList,
        ];

        return new Response(
            $this->twig->render('project/projects_list.twig', $context)
        );
    }
}
