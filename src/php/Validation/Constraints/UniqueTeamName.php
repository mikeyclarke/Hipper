<?php
declare(strict_types=1);

namespace Hipper\Validation\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;

class UniqueTeamName extends Constraint
{
    public $message = 'A team named "{{ string }}" already exists.';
    public $organizationId;

    public function __construct(
        $options = null
    ) {
        parent::__construct($options);

        if (null === $this->organizationId) {
            throw new MissingOptionsException(
                sprintf('"organizationId" must be given for constraint %s', __CLASS__),
                ['organizationId']
            );
        }
    }
}
