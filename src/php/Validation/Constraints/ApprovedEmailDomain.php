<?php
declare(strict_types=1);

namespace Hipper\Validation\Constraints;

use Hipper\Organization\OrganizationModel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;

class ApprovedEmailDomain extends Constraint
{
    public $message = 'Email address has an invalid domain';
    public $selfServeSignupDisallowedMessage = 'You’ll need an invite to join {{ organization_name }} on Hipper';
    public $invalidDomainMessage = '“{{ domain }}” is not an approved sign-up domain';
    public $organization;

    public function __construct(
        $options = null
    ) {
        parent::__construct($options);

        if (!$this->organization instanceof OrganizationModel) {
            throw new MissingOptionsException(
                sprintf('"organization" must be given for constraint %s', __CLASS__),
                ['organization']
            );
        }
    }
}
