<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Person;

use Hipper\Team\TeamRepository;
use Hipper\Team\TeamsListFormatter;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class PersonTeamMembershipsController
{
    private TeamRepository $teamRepository;
    private TeamsListFormatter $teamsListFormatter;
    private TimeZoneFromRequest $timeZoneFromRequest;
    private Twig $twig;

    public function __construct(
        TeamRepository $teamRepository,
        TeamsListFormatter $teamsListFormatter,
        TimeZoneFromRequest $timeZoneFromRequest,
        Twig $twig
    ) {
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
            'team_memberships' => $formattedTeamMemberships,
        ];

        return new Response(
            $this->twig->render('person/person_team_memberships.twig', $context)
        );
    }
}
