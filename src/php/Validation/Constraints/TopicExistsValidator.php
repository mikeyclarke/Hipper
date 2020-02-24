<?php
declare(strict_types=1);

namespace Hipper\Validation\Constraints;

use Hipper\Topic\TopicModel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class TopicExistsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof TopicExists) {
            throw new UnexpectedTypeException($constraint, TopicExists::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (null === $constraint->topic || !$constraint->topic instanceof TopicModel) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ topic_id }}', $value)
                ->addViolation();
        }
    }
}
