<?php
declare(strict_types=1);

namespace Hipper\SignUp\SignUpValidation;

use Hipper\Organization\OrganizationModel;
use Hipper\Validation\ConstraintViolationListFormatter;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InvitationSignUpValidator
{
    private ValidatorInterface $validatorInterface;

    public function __construct(
        ValidatorInterface $validatorInterface
    ) {
        $this->validatorInterface = $validatorInterface;
    }

    public function validate(array $input): void
    {
        $this->validateInput($input);
    }

    private function validateInput(array $input): void
    {
        $constraints = [
            'name' => new Required([
                new Length([
                    'min' => 3,
                    'max' => 100,
                ]),
            ]),
            'password' => new Required([
                new Length([
                    'min' => 8,
                    'max' => 160,
                ]),
            ]),
            'terms_agreed' => new Required([
                new NotBlank,
                new IsTrue,
            ]),
            'invite_id' => new Required([
                new Uuid,
            ]),
            'invite_token' => new Required([
                new NotBlank,
            ]),
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
