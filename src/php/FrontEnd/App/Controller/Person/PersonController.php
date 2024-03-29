<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Person;

use Hipper\Activity\ActivityFeedFormatter;
use Hipper\Activity\ActivityRepository;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class PersonController
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
        $person = $request->attributes->get('person');
        $timeZone = $this->timeZoneFromRequest->get($request);

        $activity = $this->activityRepository->getPersonActivity($person->getId(), $currentUser->getOrganizationId());
        $activityFeed = $this->activityFeedFormatter->format($organization, $currentUser, $timeZone, $activity);

        $context = [
            'activity_feed' => $activityFeed,
            'person' => $person,
            'person_is_current_user' => ($person->getId() === $currentUser->getId()),
        ];

        return new Response(
            $this->twig->render('person/person_index.twig', $context)
        );
    }
}
