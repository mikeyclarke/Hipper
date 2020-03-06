<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Organization;

use Hipper\Activity\ActivityFeedFormatter;
use Hipper\Activity\ActivityRepository;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class OrganizationHomeController
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
        $organization = $request->attributes->get('organization');
        $timeZone = $this->timeZoneFromRequest->get($request);

        $activity = $this->activityRepository->getActivityRelevantToUser($currentUser->getOrganizationId());
        $activityFeed = $this->activityFeedFormatter->format($organization, $currentUser, $timeZone, $activity);

        $context = [
            'activity_feed' => $activityFeed,
            'html_title' => 'Home',
        ];

        return new Response(
            $this->twig->render('organization/organization_home.twig', $context)
        );
    }
}
