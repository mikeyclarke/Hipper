<?php
declare(strict_types=1);

namespace Hipper\Validation\Constraints;

use Hipper\Invite\InviteRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UniqueInviteValidator extends ConstraintValidator
{
    private $inviteRepository;

    public function __construct(
        InviteRepository $inviteRepository
    ) {
        $this->inviteRepository = $inviteRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueInvite) {
            throw new UnexpectedTypeException($constraint, UniqueInvite::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if ($this->inviteRepository->existsWithEmailAddress($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
