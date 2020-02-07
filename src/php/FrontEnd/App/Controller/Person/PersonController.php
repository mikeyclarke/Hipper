<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Person;

use Hipper\Project\ProjectRepository;
use Hipper\Project\ProjectsListFormatter;
use Hipper\Team\TeamRepository;
use Hipper\Team\TeamsListFormatter;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class PersonController
{
    private ProjectRepository $projectRepository;
    private ProjectsListFormatter $projectsListFormatter;
    private TeamRepository $teamRepository;
    private TeamsListFormatter $teamsListFormatter;
    private TimeZoneFromRequest $timeZoneFromRequest;
    private Twig $twig;

    public function __construct(
        ProjectRepository $projectRepository,
        ProjectsListFormatter $projectsListFormatter,
        TeamRepository $teamRepository,
        TeamsListFormatter $teamsListFormatter,
        TimeZoneFromRequest $timeZoneFromRequest,
        Twig $twig
    ) {
        $this->projectRepository = $projectRepository;
        $this->projectsListFormatter = $projectsListFormatter;
        $this->teamRepository = $teamRepository;
        $this->teamsListFormatter = $teamsListFormatter;
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

        $teamMemberships = $this->teamRepository->getAllWithMappingForPerson(
            $person->getId(),
            $organization->getId()
        );
        $formattedTeamMemberships = $this->teamsListFormatter->format(
            $organization,
            $teamMemberships,
            $timeZone
        );

        $context = [
            'person' => $person,
            'person_is_current_user' => ($person->getId() === $currentUser->getId()),
            'project_memberships' => $formattedProjectMemberships,
            'team_memberships' => $formattedTeamMemberships,
        ];

        return new Response(
            $this->twig->render('person/person_index.twig', $context)
        );
    }
}
