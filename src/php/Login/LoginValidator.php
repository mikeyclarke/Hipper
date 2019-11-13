<?php
declare(strict_types=1);

namespace Hipper\Login;

use Hipper\Validation\ConstraintViolationListFormatter;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LoginValidator
{
    private $validatorInterface;

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
            'email_address' => [
                new Required([
                    'constraints' => [
                        new NotBlank([
                            'message' => 'We’ll need your email address to sign you in.',
                        ]),
                    ],
                ]),
            ],
            'password' => [
                new Required([
                    'constraints' => [
                        new NotBlank([
                            'message' => 'We’ll need your password to sign you in.',
                        ]),
                    ],
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
