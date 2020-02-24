<?php
declare(strict_types=1);

namespace Hipper\Validation\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;

class TopicExists extends Constraint
{
    public $message = 'Topic "{{ topic_id }}" not found';
    public $topic;

    public function __construct(
        $options = null
    ) {
        parent::__construct($options);
    }
}
