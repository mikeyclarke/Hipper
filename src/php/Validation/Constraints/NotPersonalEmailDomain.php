<?php
declare(strict_types=1);

namespace Hipper\Validation\Constraints;

use Symfony\Component\Validator\Constraint;

class NotPersonalEmailDomain extends Constraint
{
    public $message = 'Accounts on the domain "{{ string }}" are issued by a personal email service.';
}
