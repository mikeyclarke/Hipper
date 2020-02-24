<?php
declare(strict_types=1);

namespace Hipper\Document;

use Hipper\Knowledgebase\KnowledgebaseModel;
use Hipper\Topic\TopicModel;
use Hipper\Validation\ConstraintViolationListFormatter;
use Hipper\Validation\Constraints\DocumentStructure;
use Hipper\Validation\Constraints\KnowledgebaseExists;
use Hipper\Validation\Constraints\TopicExists;
use Hipper\Validation\Exception\ValidationException;
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

    public function validate(
        array $input,
        ?KnowledgebaseModel $knowledgebase,
        ?TopicModel $topic,
        bool $isNew = false
    ): void {
        $this->validateInput($input, $knowledgebase, $topic, $isNew);
    }

    private function validateInput(
        array $input,
        ?KnowledgebaseModel $knowledgebase,
        ?TopicModel $topic,
        bool $isNew
    ): void {
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
            'knowledgebase_id' => [
                new NotBlank([
                    'message' => 'Knowledgebase ID can’t be blank',
                ]),
                new KnowledgebaseExists([
                    'knowledgebase' => $knowledgebase,
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
            'topic_id' => [
                new TopicExists([
                    'topic' => $topic,
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
