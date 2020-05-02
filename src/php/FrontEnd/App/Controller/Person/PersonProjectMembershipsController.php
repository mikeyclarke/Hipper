<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Person;

use Hipper\Project\ProjectRepository;
use Hipper\Project\ProjectsListFormatter;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class PersonProjectMembershipsController
{
    private ProjectRepository $projectRepository;
    private ProjectsListFormatter $projectsListFormatter;
    private TimeZoneFromRequest $timeZoneFromRequest;
    private Twig $twig;

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
        $currentUser = $request->attributes->get('current_user');
        $organization = $request->attributes->get('organization');
        $person = $request->attributes->get('person');
        $timeZone = $this->timeZoneFromRequest->get($request);

        $projectMemberships = $this->projectRepository->getAllWithMappingForPerson(
            $person->getId(),
            $organization->getId()
        );
        $formattedProjectMemberships = $this->projectsListFormatter->format(
            $organization,
            $projectMemberships,
            $timeZone
        );

        $context = [
            'person' => $person,
            'person_is_current_user' => ($person->getId() === $currentUser->getId()),
            'project_memberships' => $formattedProjectMemberships,
        ];

        return new Response(
            $this->twig->render('person/person_project_memberships.twig', $context)
        );
    }
}
