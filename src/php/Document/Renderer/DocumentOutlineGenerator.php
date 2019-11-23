<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer;

class DocumentOutlineGenerator
{
    public function generate(array $doc): array
    {
        $outline = [];

        foreach ($doc['content'] as $node) {
            if (is_array($node) && isset($node['type']) && 'heading' === $node['type'] && isset($node['content'])) {
                $id = $node['html_id'] ?? null;
                $text = '';

                foreach ($node['content'] as $childNode) {
                    if (isset($childNode['type']) && 'text' === $childNode['type'] && !empty($childNode['text'])) {
                        $text .= $childNode['text'];
                    }
                }

                if (null === $id || empty($text)) {
                    continue;
                }

                $outline[] = [
                    'id' => $id,
                    'level' => $node['attrs']['level'] ?? 1,
                    'text' => $text,
                ];
            }
        }

        return $outline;
    }
}
