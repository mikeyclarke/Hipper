<?php
declare(strict_types=1);

namespace Hipper\Validation\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class SignUpAuthorizationPhraseValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof SignUpAuthorizationPhrase) {
            throw new UnexpectedTypeException($constraint, SignUpAuthorizationPhrase::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if ($constraint->authorizationRequest->getVerificationPhrase() !== $value) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
