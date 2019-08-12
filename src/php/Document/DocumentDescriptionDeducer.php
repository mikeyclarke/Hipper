<?php
declare(strict_types=1);

namespace Hipper\Document;

class DocumentDescriptionDeducer
{
    const MAX_NODE_SEARCHES = 3;
    const MIN_STRING_LENGTH = 15;
    const MAX_STRING_LENGTH = 150;

    public function deduce(array $document): ?string
    {
        $result = $this->findEarlyParagraphText($document);
        if (null === $result) {
            return null;
        }

        return mb_strimwidth($result, 0, self::MAX_STRING_LENGTH, '…');
    }

    private function findEarlyParagraphText(array $document): ?string
    {
        if (!isset($document['type']) || $document['type'] !== 'doc') {
            return null;
        }

        if (!isset($document['content']) || empty($document['content'])) {
            return null;
        }

        $maxChildNodesToSearch = min(self::MAX_NODE_SEARCHES, count($document['content']));
        for ($i = 0; $i < $maxChildNodesToSearch; $i++) {
            $node = $document['content'][$i];
            if (isset($node['type']) && $node['type'] === 'paragraph' && isset($node['content'])) {
                $text = '';
                foreach ($node['content'] as $child) {
                    if (isset($node['type']) && $child['type'] === 'text' && isset($child['text'])) {
                        $text .= $child['text'];
                    }
                    if (mb_strlen($text) > self::MAX_STRING_LENGTH) {
                        return $text;
                    }
                }
                if (mb_strlen($text) > self::MIN_STRING_LENGTH) {
                    return $text;
                }
            }
        }
        return null;
    }
}
