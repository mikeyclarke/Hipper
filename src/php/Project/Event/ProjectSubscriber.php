<?php
declare(strict_types=1);

namespace Hipper\Project\Event;

use Hipper\Activity\ActivityCreator;
use Hipper\Project\Event\ProjectCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectSubscriber implements EventSubscriberInterface
{
    private const PROJECT_CREATED_ACTIVITY_TYPE = 'project_created';

    private ActivityCreator $activityCreator;

    public function __construct(
        ActivityCreator $activityCreator
    ) {
        $this->activityCreator = $activityCreator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProjectCreatedEvent::NAME => 'onProjectCreated',
        ];
    }

    public function onProjectCreated(ProjectCreatedEvent $event): void
    {
        $project = $event->getProject();
        $properties = [
            'project_name' => $project->getName(),
            'project_url_id' => $project->getUrlId(),
        ];

        $this->activityCreator->create(
            $event->getCreator(),
            self::PROJECT_CREATED_ACTIVITY_TYPE,
            $properties,
            null,
            null,
            null,
            $project->getId()
        );
    }
}
