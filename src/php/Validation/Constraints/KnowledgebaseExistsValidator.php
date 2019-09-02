<?php
declare(strict_types=1);

namespace Hipper\Validation\Constraints;

use Hipper\Knowledgebase\KnowledgebaseModel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class KnowledgebaseExistsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof KnowledgebaseExists) {
            throw new UnexpectedTypeException($constraint, KnowledgebaseExists::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!($constraint->knowledgebase instanceof KnowledgebaseModel)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ knowledgebase_id }}', $value)
                ->addViolation();
        }
    }
}
