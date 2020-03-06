<?php
declare(strict_types=1);

namespace Hipper\Organization\Event;

use Hipper\Person\PersonModel;
use Symfony\Contracts\EventDispatcher\Event;

class OrganizationCreatedEvent extends Event
{
    public const NAME = 'organization.created';

    protected PersonModel $creator;

    public function __construct(
        PersonModel $creator
    ) {
        $this->creator = $creator;
    }

    public function getCreator(): PersonModel
    {
        return $this->creator;
    }
}
