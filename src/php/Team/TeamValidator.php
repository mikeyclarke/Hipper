<?php
declare(strict_types=1);

namespace Lithos\Team;

use Lithos\Validation\ConstraintViolationListFormatter;
use Lithos\Validation\Constraints\UniqueTeamName;
use Lithos\Validation\Exception\ValidationException;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TeamValidator
{
    private $validatorInterface;

    public function __construct(
        ValidatorInterface $validatorInterface
    ) {
        $this->validatorInterface = $validatorInterface;
    }

    public function validate(array $input, string $organizationId, bool $isNew = false): void
    {
        $this->validateInput($organizationId, $input, $isNew);
    }

    private function validateInput(string $organizationId, array $input, bool $isNew): void
    {
        $requiredOnCreate = ['name'];
        $constraints = [
            'name' => [
                new NotBlank([
                    'message' => 'A team can’t have a blank name.',
                ]),
                new Length([
                    'min' => 2,
                    'max' => 100,
                    'minMessage' => 'Team names can’t be less than {{ limit }} characters long.',
                    'maxMessage' => 'Team names can’t be more than {{ limit }} characters long.',
                ]),
                new UniqueTeamName([
                    'organizationId' => $organizationId,
                ]),
            ],
            'description' => [
                new Length([
                    'max' => 300,
                    'maxMessage' => 'Team descriptions can’t be more than {{ limit }} characters long.',
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

        $collectionConstraint = new Collection($constraints);

        $violations = $this->validatorInterface->validate($input, $collectionConstraint);

        if (count($violations) > 0) {
            throw new ValidationException(
                ConstraintViolationListFormatter::format($violations)
            );
        }
    }
}
