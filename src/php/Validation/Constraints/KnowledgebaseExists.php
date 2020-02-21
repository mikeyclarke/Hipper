<?php
declare(strict_types=1);

namespace Hipper\Validation\Constraints;

use Symfony\Component\Validator\Constraint;

class KnowledgebaseExists extends Constraint
{
    public $message = 'Knowledgebase "{{ knowledgebase_id }}" not found';
    public $knowledgebase;

    public function __construct(
        $options = null
    ) {
        parent::__construct($options);
    }
}
