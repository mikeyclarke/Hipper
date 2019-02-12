<?php
declare(strict_types=1);

namespace Lithos\Person;

use Lithos\Validation\ConstraintViolationListFormatter;
use Lithos\Validation\Exception\ValidationException;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validation;

class PersonValidator
{
    private $personRepository;

    public function __construct(
        PersonRepository $personRepository
    ) {
        $this->personRepository = $personRepository;
    }

    public function validate(array $input, bool $isNew = false): void
    {
        $this->validateInput($input, $isNew);
        $this->validateUniqueEmailAddress($input);
    }

    private function validateUniqueEmailAddress(array $input): void
    {
        if (!isset($input['email_address'])) {
            return;
        }

        if (null !== $this->personRepository->findOneByEmailAddress($input['email_address'])) {
            throw new ValidationException([
                'email_address' => [
                    sprintf('Email address %s already in use.', $input['email_address']),
                ]
            ]);
        }
    }

    private function validateInput(array $input, bool $isNew): void
    {
        $requiredOnCreate = ['name', 'email_address', 'password', 'terms_agreed'];
        $constraints = [
            'name' => [
                new NotBlank,
                new Length([
                    'min' => 3,
                    'max' => 100,
                ]),
            ],
            'email_address' => [
                new NotBlank,
                new Email,
            ],
            'password' => [
                new NotBlank,
                new Length([
                    'min' => 8,
                    'max' => 160,
                ]),
            ],
            'email_address_verified' => [
                new Type([
                    'type' => 'bool',
                ]),
            ],
            'terms_agreed' => [
                new IsTrue,
            ],
        ];

        foreach ($constraints as $key => &$value) {
            if (in_array($key, $requiredOnCreate) && $isNew) {
                $constraints[$key] = [new Required($constraints[$key])];
            } else {
                $constraints[$key] = [new Optional($constraints[$key])];
            }
        }

        $validator = Validation::createValidator();
        $collectionConstraint = new Collection($constraints);

        $violations = $validator->validate($input, $collectionConstraint);

        if (count($violations) > 0) {
            throw new ValidationException(
                ConstraintViolationListFormatter::format($violations)
            );
        }
    }
}
