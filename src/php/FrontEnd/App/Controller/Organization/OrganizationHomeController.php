<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Organization;

use Hipper\Activity\ActivityFeedFormatter;
use Hipper\Activity\ActivityRepository;
use Hipper\Project\ProjectRepository;
use Hipper\Project\ProjectsListFormatter;
use Hipper\Team\TeamRepository;
use Hipper\Team\TeamsListFormatter;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class OrganizationHomeController
{
    private ActivityFeedFormatter $activityFeedFormatter;
    private ActivityRepository $activityRepository;
    private ProjectRepository $projectRepository;
    private ProjectsListFormatter $projectsListFormatter;
    private TeamRepository $teamRepository;
    private TeamsListFormatter $teamsListFormatter;
    private TimeZoneFromRequest $timeZoneFromRequest;
    private Twig $twig;

    public function __construct(
        ActivityFeedFormatter $activityFeedFormatter,
        ActivityRepository $activityRepository,
        ProjectRepository $projectRepository,
        ProjectsListFormatter $projectsListFormatter,
        TeamRepository $teamRepository,
        TeamsListFormatter $teamsListFormatter,
        TimeZoneFromRequest $timeZoneFromRequest,
        Twig $twig
    ) {
        $this->activityFeedFormatter = $activityFeedFormatter;
        $this->activityRepository = $activityRepository;
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
        $timeZone = $this->timeZoneFromRequest->get($request);

        $projectMemberships = $this->projectRepository->getAllWithMappingForPerson(
            $currentUser->getId(),
            $organization->getId()
        );
        $formattedProjectMemberships = $this->projectsListFormatter->format(
            $organization,
            $projectMemberships,
            $timeZone
        );

        $teamMemberships = $this->teamRepository->getAllWithMappingForPerson(
            $currentUser->getId(),
            $organization->getId()
        );
        $formattedTeamMemberships = $this->teamsListFormatter->format(
            $organization,
            $teamMemberships,
            $timeZone
        );

        $activity = $this->activityRepository->getActivityRelevantToUser($currentUser->getOrganizationId());
        $activityFeed = $this->activityFeedFormatter->format($organization, $currentUser, $timeZone, $activity);

        $context = [
            'activity_feed' => $activityFeed,
            'html_title' => 'Home',
            'project_memberships' => $formattedProjectMemberships,
            'team_memberships' => $formattedTeamMemberships,
        ];

        return new Response(
            $this->twig->render('organization/organization_home.twig', $context)
        );
    }
}
