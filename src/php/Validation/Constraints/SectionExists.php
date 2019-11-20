<?php
declare(strict_types=1);

namespace Hipper\Validation\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;

class SectionExists extends Constraint
{
    public $message = 'Section "{{ section_id }}" not found';
    public $section;

    public function __construct(
        $options = null
    ) {
        parent::__construct($options);
    }
}
