<?php
declare(strict_types=1);

namespace Hipper\Validation\Constraints;

use Hipper\Person\Exception\MalformedEmailAddressException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ApprovedEmailDomainValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ApprovedEmailDomain) {
            throw new UnexpectedTypeException($constraint, ApprovedEmailDomain::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $organization = $constraint->organization;
        if (!$organization->isApprovedEmailDomainSignupAllowed()) {
            $this->context->buildViolation($constraint->selfServeSignupDisallowedMessage)
                ->setParameter('{{ organization_name }}', $organization->getName())
                ->addViolation();
            return;
        }

        $domain = false;
        $parts = explode('@', $value);
        if (count($parts) === 2) {
            $domain = $parts[1];
        }

        if (false === $domain) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
            return;
        }

        if (null === $organization->getApprovedEmailDomains() ||
            !in_array($domain, $organization->getApprovedEmailDomains())
        ) {
            $this->context->buildViolation($constraint->invalidDomainMessage)
                ->setParameter('{{ domain }}', $domain)
                ->addViolation();
        }
    }
}
