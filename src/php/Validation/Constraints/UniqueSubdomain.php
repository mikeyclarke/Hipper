<?php
declare(strict_types=1);

namespace Lithos\Validation\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueSubdomain extends Constraint
{
    public $message = 'The subdomain "{{ string }}" has already been taken.';
}
