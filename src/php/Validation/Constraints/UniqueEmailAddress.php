<?php
declare(strict_types=1);

namespace Lithos\Validation\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueEmailAddress extends Constraint
{
    public $message = 'A person already exists with the email address "{{ string }}".';
}
