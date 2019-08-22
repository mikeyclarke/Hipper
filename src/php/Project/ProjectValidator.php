<?php
declare(strict_types=1);

namespace Hipper\Project;

use Hipper\Validation\ConstraintViolationListFormatter;
use Hipper\Validation\Constraints\UniqueProjectName;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProjectValidator
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
                    'message' => 'A project can’t have a blank name.',
                ]),
                new Length([
                    'min' => 2,
                    'max' => 100,
                    'minMessage' => 'Project names can’t be less than {{ limit }} characters long.',
                    'maxMessage' => 'Project names can’t be more than {{ limit }} characters long.',
                ]),
                new UniqueProjectName([
                    'organizationId' => $organizationId,
                ]),
            ],
            'description' => [
                new Length([
                    'max' => 300,
                    'maxMessage' => 'Project descriptions can’t be more than {{ limit }} characters long.',
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
