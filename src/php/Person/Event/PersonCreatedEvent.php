<?php
declare(strict_types=1);

namespace Hipper\Person\Event;

use Hipper\Person\PersonModel;
use Symfony\Contracts\EventDispatcher\Event;

class PersonCreatedEvent extends Event
{
    public const NAME = 'person.created';

    private PersonModel $person;

    public function __construct(
        PersonModel $person
    ) {
        $this->person = $person;
    }

    public function getPerson(): PersonModel
    {
        return $this->person;
    }
}
