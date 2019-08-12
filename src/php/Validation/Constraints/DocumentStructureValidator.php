<?php
declare(strict_types=1);

namespace Hipper\Validation\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DocumentStructureValidator extends ConstraintValidator
{
    const BLOCK_NODE_KEYS = ['type', 'attrs', 'content'];
    const INLINE_NODES = ['image', 'hard_break'];
    const INLINE_NODE_KEYS = ['type', 'attrs', 'marks'];
    const MARK_KEYS = ['type', 'attrs'];
    const TEXT_NODE_KEYS = ['type', 'text', 'marks'];

    private $documentAllowedMarks;
    private $documentAllowedNodes;

    public function __construct(
        array $documentAllowedMarks,
        array $documentAllowedNodes
    ) {
        $this->documentAllowedMarks = $documentAllowedMarks;
        $this->documentAllowedNodes = $documentAllowedNodes;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof DocumentStructure) {
            throw new UnexpectedTypeException($constraint, DocumentStructure::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!$this->nodeIsValid($value, 'doc')) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }

    private function nodeIsValid($node, string $requiredType = null): bool
    {
        if (!is_array($node)) {
            return false;
        }

        if (!isset($node['type'])) {
            return false;
        }

        $allowedNodeTypes = $this->documentAllowedNodes;
        if (null !== $requiredType) {
            $allowedNodeTypes = [$requiredType];
        }

        if (!in_array($node['type'], $allowedNodeTypes)) {
            return false;
        }

        $allowedKeys = $this->getAllowedNodeKeys($node['type']);
        $diff = array_diff_key($node, array_flip($allowedKeys));

        if (!empty($diff)) {
            return false;
        }

        if (isset($node['marks']) && in_array('marks', $allowedKeys)) {
            if (!is_array($node['marks'])) {
                return false;
            }
            foreach ($node['marks'] as $mark) {
                $result = $this->markIsValid($mark);
                if (false === $result) {
                    return false;
                }
            }
        }

        if (isset($node['content']) && in_array('content', $allowedKeys)) {
            if (!is_array($node['content'])) {
                return false;
            }
            foreach ($node['content'] as $content) {
                $valid = $this->nodeIsValid($content);
                if (false === $valid) {
                    return false;
                }
            }
        }

        if (isset($node['attrs']) && in_array('attrs', $allowedKeys)) {
            if (!is_array($node['attrs'])) {
                return false;
            }
        }

        return true;
    }

    private function markIsValid($mark): bool
    {
        if (!is_array($mark)) {
            return false;
        }

        if (!isset($mark['type'])) {
            return false;
        }

        if (!in_array($mark['type'], $this->documentAllowedMarks)) {
            return false;
        }

        $allowedKeys = self::MARK_KEYS;
        $diff = array_diff_key($mark, array_flip($allowedKeys));
        if (!empty($diff)) {
            return false;
        }

        if (isset($mark['attrs']) && in_array('attrs', $allowedKeys)) {
            if (!is_array($mark['attrs'])) {
                return false;
            }
        }

        return true;
    }

    private function getAllowedNodeKeys(string $nodeType): array
    {
        if ($nodeType === 'text') {
            return self::TEXT_NODE_KEYS;
        }

        if (in_array($nodeType, self::INLINE_NODES)) {
            return self::INLINE_NODE_KEYS;
        }

        return self::BLOCK_NODE_KEYS;
    }
}
