<?php
declare(strict_types=1);

namespace Hipper\Project\Event;

use Hipper\Person\PersonModel;
use Hipper\Project\ProjectModel;
use Symfony\Contracts\EventDispatcher\Event;

class ProjectCreatedEvent extends Event
{
    public const NAME = 'project.created';

    protected ProjectModel $project;
    protected PersonModel $creator;

    public function __construct(
        ProjectModel $project,
        PersonModel $creator
    ) {
        $this->project = $project;
        $this->creator = $creator;
    }

    public function getProject(): ProjectModel
    {
        return $this->project;
    }

    public function getCreator(): PersonModel
    {
        return $this->creator;
    }
}
