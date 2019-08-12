<?php
declare(strict_types=1);

namespace Hipper\Validation\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueInvite extends Constraint
{
    public $message = 'An invite already exists with the email address "{{ string }}".';
}
