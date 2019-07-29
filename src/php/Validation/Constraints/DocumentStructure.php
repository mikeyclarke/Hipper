<?php
declare(strict_types=1);

namespace Lithos\Validation\Constraints;

use Symfony\Component\Validator\Constraint;

class DocumentStructure extends Constraint
{
    public $message = 'Document content does not meet the required structure.';
}
