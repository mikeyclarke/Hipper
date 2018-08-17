<?php
namespace hleo\Person;

use hleo\Validation\ConstraintViolationListFormatter;
use hleo\Validation\Exception\ValidationException;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validation;

class PersonValidator
{
    public function validate(array $input, bool $isNew = false)
    {
        $this->validateInput($input, $isNew);
    }

    public function validateInput(array $input, bool $isNew)
    {
        $requiredOnCreate = ['name', 'email_address', 'password'];
        $constraints = [
            'name' => [
                new NotBlank,
            ],
            'email_address' => [
                new NotBlank,
                new Email,
            ],
            'password' => [
                new NotBlank,
                new Length([
                    'min' => 8,
                    'max' => 4096,
                ]),
            ],
            'email_address_verified' => [
                new Type([
                    'type' => 'bool',
                ]),
            ],
            'role' => [
                new Choice([
                    'choices' => ['owner', 'admin', 'member'],
                ]),
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
