<?php
declare(strict_types=1);

namespace Lithos\Validation\Constraints;

use Lithos\Organization\OrganizationRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UniqueSubdomainValidator extends ConstraintValidator
{
    private $organizationRepository;

    public function __construct(
        OrganizationRepository $organizationRepository
    ) {
        $this->organizationRepository = $organizationRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueSubdomain) {
            throw new UnexpectedTypeException($constraint, UniqueSubdomain::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if ($this->organizationRepository->existsWithSubdomain($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
