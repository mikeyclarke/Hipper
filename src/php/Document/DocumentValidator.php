<?php
declare(strict_types=1);

namespace Lithos\Document;

use Lithos\Validation\ConstraintViolationListFormatter;
use Lithos\Validation\Constraints\DocumentStructure;
use Lithos\Validation\Constraints\KnowledgebaseExistsInOrganization;
use Lithos\Validation\Exception\ValidationException;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DocumentValidator
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
        $requiredOnCreate = ['name', 'knowledgebase_id'];
        $constraints = [
            'name' => [
                new NotBlank([
                    'message' => 'A document can’t have a blank name',
                ]),
                new Length([
                    'min' => 2,
                    'max' => 150,
                    'minMessage' => 'Document names can’t be less than {{ limit }} characters long.',
                    'maxMessage' => 'Document names can’t be more than {{ limit }} characters long.',
                ]),
            ],
            'description' => [
                new Length([
                    'max' => 300,
                    'maxMessage' => 'Document summary can’t be more than {{ limit }} characters long.',
                ]),
            ],
            'content' => [
                new DocumentStructure([
                    'message' => 'Content invalid',
                ]),
            ],
            'knowledgebase_id' => [
                new NotBlank([
                    'message' => 'Knowledgebase ID can’t be blank',
                ]),
                new KnowledgebaseExistsInOrganization([
                    'organizationId' => $organizationId,
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
