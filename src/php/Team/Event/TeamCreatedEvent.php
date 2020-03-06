<?php
declare(strict_types=1);

namespace Hipper\Team\Event;

use Hipper\Person\PersonModel;
use Hipper\Team\TeamModel;
use Symfony\Contracts\EventDispatcher\Event;

class TeamCreatedEvent extends Event
{
    public const NAME = 'team.created';

    protected TeamModel $team;
    protected PersonModel $creator;

    public function __construct(
        TeamModel $team,
        PersonModel $creator
    ) {
        $this->team = $team;
        $this->creator = $creator;
    }

    public function getTeam(): TeamModel
    {
        return $this->team;
    }

    public function getCreator(): PersonModel
    {
        return $this->creator;
    }
}
