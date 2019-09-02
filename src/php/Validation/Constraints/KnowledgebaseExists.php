<?php
declare(strict_types=1);

namespace Hipper\Validation\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;

class KnowledgebaseExists extends Constraint
{
    public $message = 'Knowledgebase "{{ knowledgebase_id }}" not found';
    public $knowledgebase;

    public function __construct(
        $options = null
    ) {
        parent::__construct($options);

        if (null === $this->knowledgebase) {
            throw new MissingOptionsException(
                sprintf('"knowledgebase" must be given for constraint %s', __CLASS__),
                ['organizationId']
            );
        }
    }
}
