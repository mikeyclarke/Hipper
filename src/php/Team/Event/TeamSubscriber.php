<?php
declare(strict_types=1);

namespace Hipper\Team\Event;

use Hipper\Activity\ActivityCreator;
use Hipper\Team\Event\TeamCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TeamSubscriber implements EventSubscriberInterface
{
    private const TEAM_CREATED_ACTIVITY_TYPE = 'team_created';

    private ActivityCreator $activityCreator;

    public function __construct(
        ActivityCreator $activityCreator
    ) {
        $this->activityCreator = $activityCreator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TeamCreatedEvent::NAME => 'onTeamCreated',
        ];
    }

    public function onTeamCreated(TeamCreatedEvent $event): void
    {
        $team = $event->getTeam();
        $properties = [
            'team_name' => $team->getName(),
            'team_url_slug' => $team->getUrlSlug(),
        ];

        $this->activityCreator->create(
            $event->getCreator(),
            self::TEAM_CREATED_ACTIVITY_TYPE,
            $properties,
            null,
            null,
            $team->getId()
        );
    }
}
