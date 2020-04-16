<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Project;

use Hipper\Activity\ActivityFeedFormatter;
use Hipper\Activity\ActivityRepository;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class ProjectController
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
        $currentUserIsInProject = $request->attributes->get('current_user_is_in_project');
        $organization = $request->attributes->get('organization');
        $project = $request->attributes->get('project');
        $timeZone = $this->timeZoneFromRequest->get($request);

        $activity = $this->activityRepository->getProjectActivity($project->getId(), $currentUser->getOrganizationId());
        $activityFeed = $this->activityFeedFormatter->format($organization, $currentUser, $timeZone, $activity);

        $context = [
            'activity_feed' => $activityFeed,
            'html_title' => $project->getName(),
            'project' => $project,
            'current_user_is_in_project' => $currentUserIsInProject,
        ];

        return new Response(
            $this->twig->render('project/project_index.twig', $context)
        );
    }
}
