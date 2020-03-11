<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Project;

use Hipper\Person\PersonRepository;
use Hipper\Person\PeopleListFormatter;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class ProjectMembersController
{
    private PersonRepository $personRepository;
    private PeopleListFormatter $peopleListFormatter;
    private TimeZoneFromRequest $timeZoneFromRequest;
    private Twig $twig;

    public function __construct(
        PersonRepository $personRepository,
        PeopleListFormatter $peopleListFormatter,
        TimeZoneFromRequest $timeZoneFromRequest,
        Twig $twig
    ) {
        $this->personRepository = $personRepository;
        $this->peopleListFormatter = $peopleListFormatter;
        $this->timeZoneFromRequest = $timeZoneFromRequest;
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $project = $request->attributes->get('project');
        $organization = $request->attributes->get('organization');
        $currentUserIsInProject = $request->attributes->get('current_user_is_in_project');
        $displayTimeZone = $this->timeZoneFromRequest->get($request);

        $people = $this->personRepository->getAllInProject($project->getId(), $organization->getId());
        $peopleList = $this->peopleListFormatter->format($organization, $people, $displayTimeZone);

        $context = [
            'html_title' => sprintf('Members – %s', $project->getName()),
            'people_list' => $peopleList,
            'current_user_is_in_project' => $currentUserIsInProject,
        ];

        return new Response(
            $this->twig->render('project/project_members.twig', $context)
        );
    }
}
