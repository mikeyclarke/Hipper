<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Team;

use Hipper\Person\PersonRepository;
use Hipper\Person\PeopleListFormatter;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class TeamMembersController
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
        $team = $request->attributes->get('team');
        $organization = $request->attributes->get('organization');
        $currentUserIsInTeam = $request->attributes->get('current_user_is_in_team');
        $displayTimeZone = $this->timeZoneFromRequest->get($request);

        $people = $this->personRepository->getAllInTeam($team->getId(), $organization->getId());
        $peopleList = $this->peopleListFormatter->format($organization, $people, $displayTimeZone);

        $context = [
            'html_title' => sprintf('Members – %s', $team->getName()),
            'people_list' => $peopleList,
            'current_user_is_in_team' => $currentUserIsInTeam,
        ];

        return new Response(
            $this->twig->render('team/team_members.twig', $context)
        );
    }
}
