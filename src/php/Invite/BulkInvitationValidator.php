<?php
declare(strict_types=1);

namespace Lithos\Invite;

use Lithos\Validation\Constraints\UniqueEmailAddress;
use Lithos\Validation\Constraints\UniqueInvite;
use Lithos\Validation\ConstraintViolationListFormatter;
use Lithos\Validation\Exception\ValidationException;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BulkInvitationValidator
{
    const MAX_BULK_INVITES = 20;

    private $validatorInterface;

    public function __construct(
        ValidatorInterface $validatorInterface
    ) {
        $this->validatorInterface = $validatorInterface;
    }

    public function validate(array $input): void
    {
        $this->validateInput($input);
        $this->validateInviteCount($input);
    }

    private function validateInviteCount(array $input): void
    {
        if (!isset($input['email_invites']) || null === $input['email_invites']) {
            return;
        }

        if (count($input['email_invites']) > self::MAX_BULK_INVITES) {
            throw new ValidationException([
                'email_invites' => [
                    sprintf(
                        'Bulk invites are limited to %d at a time. %d invites were requested.',
                        self::MAX_BULK_INVITES,
                        count($input['email_invites'])
                    )
                ]
            ]);
        }
    }

    private function validateInput(array $input): void
    {
        $constraints = [
            'email_invites' => [
                new All([
                    new NotBlank,
                    new Email,
                    new UniqueInvite,
                    new UniqueEmailAddress,
                ]),
            ],
        ];

        $collectionConstraint = new Collection($constraints);

        $violations = $this->validatorInterface->validate($input, $collectionConstraint);

        if (count($violations) > 0) {
            throw new ValidationException(
                ConstraintViolationListFormatter::format($violations)
            );
        }
    }
}
