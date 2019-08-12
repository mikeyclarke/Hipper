<?php
declare(strict_types=1);

namespace Hipper\Validation\Constraints;

use Hipper\Team\TeamRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UniqueTeamNameValidator extends ConstraintValidator
{
    private $teamRepository;

    public function __construct(
        TeamRepository $teamRepository
    ) {
        $this->teamRepository = $teamRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueTeamName) {
            throw new UnexpectedTypeException($constraint, UniqueTeamName::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $organizationId = $constraint->organizationId;

        if ($this->teamRepository->existsWithName($organizationId, $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
