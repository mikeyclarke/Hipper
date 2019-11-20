<?php
declare(strict_types=1);

namespace Hipper\Validation\Constraints;

use Hipper\Section\SectionModel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class SectionExistsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof SectionExists) {
            throw new UnexpectedTypeException($constraint, SectionExists::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (null === $constraint->section || !$constraint->section instanceof SectionModel) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ section_id }}', $value)
                ->addViolation();
        }
    }
}
