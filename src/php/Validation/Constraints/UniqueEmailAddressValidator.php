<?php
declare(strict_types=1);

namespace Hipper\Validation\Constraints;

use Hipper\Person\PersonRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UniqueEmailAddressValidator extends ConstraintValidator
{
    private $personRepository;

    public function __construct(
        PersonRepository $personRepository
    ) {
        $this->personRepository = $personRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueEmailAddress) {
            throw new UnexpectedTypeException($constraint, UniqueEmailAddress::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if ($this->personRepository->existsWithEmailAddress($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
