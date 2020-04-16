<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Team;

use Hipper\Activity\ActivityFeedFormatter;
use Hipper\Activity\ActivityRepository;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class TeamController
{
    private ActivityFeedFormatter $activityFeedFormatter;
    private ActivityRepository $activityRepository;
    private TimeZoneFromRequest $timeZoneFromRequest;
    private Twig $twig;

    public function __construct(
        ActivityFeedFormatter $activityFeedFormatter,
        ActivityRepository $activityRepository,
        TimeZoneFromRequest $timeZoneFromRequest,
        Twig $twig
    ) {
        $this->activityFeedFormatter = $activityFeedFormatter;
        $this->activityRepository = $activityRepository;
        $this->timeZoneFromRequest = $timeZoneFromRequest;
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $currentUser = $request->attributes->get('current_user');
        $currentUserIsInTeam = $request->attributes->get('current_user_is_in_team');
        $organization = $request->attributes->get('organization');
        $team = $request->attributes->get('team');
        $timeZone = $this->timeZoneFromRequest->get($request);

        $activity = $this->activityRepository->getTeamActivity($team->getId(), $currentUser->getOrganizationId());
        $activityFeed = $this->activityFeedFormatter->format($organization, $currentUser, $timeZone, $activity);

        $context = [
            'activity_feed' => $activityFeed,
            'html_title' => $team->getName(),
            'team' => $team,
            'current_user_is_in_team' => $currentUserIsInTeam,
        ];

        return new Response(
            $this->twig->render('team/team_index.twig', $context)
        );
    }
}
