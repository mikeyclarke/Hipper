<?php
declare(strict_types=1);

namespace Lithos\Validation\Constraints;

use Lithos\Knowledgebase\KnowledgebaseRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class KnowledgebaseExistsInOrganizationValidator extends ConstraintValidator
{
    private $knowledgebaseRepository;

    public function __construct(
        KnowledgebaseRepository $knowledgebaseRepository
    ) {
        $this->knowledgebaseRepository = $knowledgebaseRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof KnowledgebaseExistsInOrganization) {
            throw new UnexpectedTypeException($constraint, KnowledgebaseExistsInOrganization::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $organizationId = $constraint->organizationId;

        if (!$this->knowledgebaseRepository->exists($organizationId, $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ knowledgebase_id }}', $value)
                ->addViolation();
        }
    }
}
