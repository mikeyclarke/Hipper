<?php
declare(strict_types=1);

namespace Hipper\Tests\Validation\Constraints;

use Hipper\Validation\Constraints\DocumentStructure;
use Hipper\Validation\Constraints\DocumentStructureValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class DocumentStructureValidatorTest extends ConstraintValidatorTestCase
{
    const ALLOWED_NODES = [
        'text', 'paragraph', 'heading', 'image', 'code_block', 'hard_break', 'horizontal_rule', 'blockquote',
        'unordered_list', 'ordered_list', 'list_item',
    ];
    const ALLOWED_MARKS = ['em', 'strong', 'link', 'code'];

    protected function createValidator()
    {
        return new DocumentStructureValidator(
            self::ALLOWED_MARKS,
            self::ALLOWED_NODES
        );
    }

    /**
     * @test
     */
    public function validExample()
    {
        $message = 'Document content does not meet the required structure.';

        $value = json_decode(
            file_get_contents(__DIR__ . '/../../../data/document_json_structure_validator_example.json'),
            true
        );
        $this->validator->validate($value, new DocumentStructure(['message' => $message]));

        $this->assertNoViolation();
    }

    /**
     * @test
     */
    public function outerNodeMustHaveType()
    {
        $message = 'Document content does not meet the required structure.';

        $value = ["content" => []];
        $this->validator->validate($value, new DocumentStructure(['message' => $message]));

        $this->buildViolation($message)
            ->assertRaised();
    }

    /**
     * @test
     */
    public function outerNodeMustBeTypeDoc()
    {
        $message = 'Document content does not meet the required structure.';

        $value = ["type" => "paragraph", "content" => []];
        $this->validator->validate($value, new DocumentStructure(['message' => $message]));

        $this->buildViolation($message)
            ->assertRaised();
    }

    /**
     * @test
     */
    public function blockNodesCannotHaveMarks()
    {
        $message = 'Document content does not meet the required structure.';

        $value = ["type" => "doc", "content" => [["type" => "paragraph", "marks" => []]]];
        $this->validator->validate($value, new DocumentStructure(['message' => $message]));

        $this->buildViolation($message)
            ->assertRaised();
    }

    /**
     * @test
     */
    public function inlineNodesCannotHaveContent()
    {
        $message = 'Document content does not meet the required structure.';

        $value = ["type" => "doc", "content" => [["type" => "image", "content" => []]]];
        $this->validator->validate($value, new DocumentStructure(['message' => $message]));

        $this->buildViolation($message)
            ->assertRaised();
    }

    /**
     * @test
     */
    public function textNodeCannotHaveContent()
    {
        $message = 'Document content does not meet the required structure.';

        $value = ["type" => "doc", "content" => [["type" => "text", "content" => []]]];
        $this->validator->validate($value, new DocumentStructure(['message' => $message]));

        $this->buildViolation($message)
            ->assertRaised();
    }

    /**
     * @test
     */
    public function inlineNodesCannotHaveText()
    {
        $message = 'Document content does not meet the required structure.';

        $value = ["type" => "doc", "content" => [["type" => "image", "text" => "👋"]]];
        $this->validator->validate($value, new DocumentStructure(['message' => $message]));

        $this->buildViolation($message)
            ->assertRaised();
    }

    /**
     * @test
     */
    public function blockNodesCannotHaveText()
    {
        $message = 'Document content does not meet the required structure.';

        $value = ["type" => "doc", "content" => [["type" => "paragraph", "text" => "👋"]]];
        $this->validator->validate($value, new DocumentStructure(['message' => $message]));

        $this->buildViolation($message)
            ->assertRaised();
    }

    /**
     * @test
     */
    public function contentMustBeAnArray()
    {
        $message = 'Document content does not meet the required structure.';

        $value = ["type" => "doc", "content" => [["type" => "paragraph", "content" => "hello"]]];
        $this->validator->validate($value, new DocumentStructure(['message' => $message]));

        $this->buildViolation($message)
            ->assertRaised();
    }

    /**
     * @test
     */
    public function marksMustBeAnArray()
    {
        $message = 'Document content does not meet the required structure.';

        $value = ["type" => "doc", "content" => [["type" => "image", "marks" => "hello"]]];
        $this->validator->validate($value, new DocumentStructure(['message' => $message]));

        $this->buildViolation($message)
            ->assertRaised();
    }

    /**
     * @test
     */
    public function attrsMustBeAnArray()
    {
        $message = 'Document content does not meet the required structure.';

        $value = ["type" => "doc", "content" => [["type" => "image", "attrs" => "foo"]]];
        $this->validator->validate($value, new DocumentStructure(['message' => $message]));

        $this->buildViolation($message)
            ->assertRaised();
    }

    /**
     * @test
     */
    public function markMustBeAnArray()
    {
        $message = 'Document content does not meet the required structure.';

        $value = ["type" => "doc", "content" => [["type" => "image", "marks" => "link"]]];
        $this->validator->validate($value, new DocumentStructure(['message' => $message]));

        $this->buildViolation($message)
            ->assertRaised();
    }

    /**
     * @test
     */
    public function markMustBeAllowed()
    {
        $message = 'Document content does not meet the required structure.';

        $value = ["type" => "doc", "content" => [["type" => "image", "marks" => [["type" => "foo"]]]]];
        $this->validator->validate($value, new DocumentStructure(['message' => $message]));

        $this->buildViolation($message)
            ->assertRaised();
    }

    /**
     * @test
     */
    public function markCannotHaveArbitraryKeys()
    {
        $message = 'Document content does not meet the required structure.';

        $value = [
            "type" => "doc",
            "content" => [
                [
                    "type" => "image",
                    "marks" => [
                        [
                            "type" => "link",
                            "some" => "thing",
                        ]
                    ]
                ]
            ]
        ];
        $this->validator->validate($value, new DocumentStructure(['message' => $message]));

        $this->buildViolation($message)
            ->assertRaised();
    }
}
