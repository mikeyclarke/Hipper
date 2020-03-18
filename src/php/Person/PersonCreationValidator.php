<?php
declare(strict_types=1);

namespace Hipper\Person;

use Hipper\Validation\Constraints\UniqueEmailAddress;
use Hipper\Validation\ConstraintViolationListFormatter;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PersonCreationValidator
{
    const ALLOWED_VALIDATION_GROUPS = ['sign_up_authentication', 'invite'];

    private $validatorInterface;

    public function __construct(
        ValidatorInterface $validatorInterface
    ) {
        $this->validatorInterface = $validatorInterface;
    }

    public function validate(array $input, string $validationGroup = null): void
    {
        if (null !== $validationGroup && !in_array($validationGroup, self::ALLOWED_VALIDATION_GROUPS)) {
            throw new \InvalidArgumentException('Unsupported validation group');
        }

        $this->validateInput($input, $validationGroup);
    }

    private function validateInput(array $input, string $validationGroup = null): void
    {
        $constraints = [
            'name' => new Required([
                new NotBlank,
                new Length([
                    'min' => 3,
                    'max' => 100,
                ]),
            ]),
            'email_address' => new Required([
                new NotBlank,
                new Email,
                new UniqueEmailAddress,
            ]),
        ];

        if (null !== $validationGroup) {
            $constraints['password'] = [
                new Required([
                    new NotBlank,
                    new Length([
                        'min' => 8,
                        'max' => 160,
                    ]),
                ]),
            ];
            $constraints['terms_agreed'] = [
                new Required([
                    new NotBlank,
                    new IsTrue,
                ]),
            ];
        }

        $collectionConstraint = new Collection($constraints);
        $violations = $this->validatorInterface->validate($input, $collectionConstraint);

        if (count($violations) > 0) {
            throw new ValidationException(
                ConstraintViolationListFormatter::format($violations)
            );
        }
    }
}
