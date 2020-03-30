<?php
declare(strict_types=1);

namespace Hipper\Validation\Constraints;

use Hipper\SignUp\SignUpAuthorizationRequestModel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;

class SignUpAuthorizationPhrase extends Constraint
{
    public $message = 'Incorrect verification phrase.';
    public $authorizationRequest;

    public function __construct(
        $options = null
    ) {
        parent::__construct($options);

        if (!$this->authorizationRequest instanceof SignUpAuthorizationRequestModel) {
            throw new MissingOptionsException(
                sprintf('"authorizationRequest" must be given for constraint %s', __CLASS__),
                'authorizationRequest'
            );
        }
    }
}
