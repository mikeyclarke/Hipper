<?php
declare(strict_types=1);

namespace Hipper\Person;

use Hipper\Validation\Constraints\UniqueEmailAddress;
use Hipper\Validation\ConstraintViolationListFormatter;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PersonCreationValidator
{
    const ALLOWED_VALIDATION_GROUPS = ['founding_member', 'invite', 'approved_email_domain'];

    private $validatorInterface;

    public function __construct(
        ValidatorInterface $validatorInterface
    ) {
        $this->validatorInterface = $validatorInterface;
    }

    public function validate(array $input, string $validationGroup): void
    {
        if (!in_array($validationGroup, self::ALLOWED_VALIDATION_GROUPS)) {
            throw new \InvalidArgumentException('Unsupported validation group');
        }

        $this->validateInput($input, $validationGroup);
    }

    private function validateInput(array $input, string $validationGroup): void
    {
        $constraints = [
            'name' => [
                new NotBlank,
                new Length([
                    'min' => 3,
                    'max' => 100,
                ]),
            ],
            'email_address' => new Required([
                'groups' => ['founding_member', 'approved_email_domain'],
                'constraints' => [
                    new NotBlank,
                    new Email,
                    new UniqueEmailAddress,
                ],
            ]),
            'password' => [
                new NotBlank,
                new Length([
                    'min' => 8,
                    'max' => 160,
                ]),
            ],
            'email_address_verified' => new Optional([
                new Type([
                    'type' => 'bool',
                ]),
            ]),
            'terms_agreed' => [
                new NotBlank,
                new IsTrue,
            ],
            'invite_id' => new Required([
                'groups' => ['invite'],
                'constraints' => [
                    new Uuid([
                        'versions' => [4]
                    ]),
                ],
            ]),
            'invite_token' => new Required([
                'groups' => ['invite'],
                'constraints' => [
                    new NotBlank,
                ],
            ]),
        ];

        foreach ($constraints as $key => $constraint) {
            if ($constraint instanceof Required) {
                if (!empty(array_diff(['Default'], $constraint->groups)) &&
                    !in_array($validationGroup, $constraint->groups)
                ) {
                    $constraints[$key] = new Optional($constraint->constraints);
                }
            }
        }

        $collectionConstraint = new Collection($constraints);
        $groups = ['Default', $validationGroup];
        $violations = $this->validatorInterface->validate($input, $collectionConstraint, $groups);

        if (count($violations) > 0) {
            throw new ValidationException(
                ConstraintViolationListFormatter::format($violations)
            );
        }
    }
}
