<?php
declare(strict_types=1);

namespace Hipper\Validation\Constraints;

use Symfony\Component\Validator\Constraint;

class NotReservedSubdomain extends Constraint
{
    public $message = 'The subdomain "{{ string }}" is reserved.';
}
