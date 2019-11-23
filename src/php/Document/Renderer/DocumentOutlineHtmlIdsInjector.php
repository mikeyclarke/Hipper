<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer;

use Hipper\Url\UrlSlugGenerator;

class DocumentOutlineHtmlIdsInjector
{
    const ID_PREFIX_CHAR = '_';

    private $urlSlugGenerator;

    public function __construct(
        UrlSlugGenerator $urlSlugGenerator
    ) {
        $this->urlSlugGenerator = $urlSlugGenerator;
    }

    public function inject(array $doc): array
    {
        $usedIds = [];
        $doc['content'] = array_map(
            function ($node) use (&$usedIds) {
                if (!is_array($node) || !isset($node['type'])) {
                    return $node;
                }

                if ($node['type'] !== 'heading') {
                    return $node;
                }

                if (!isset($node['content'])) {
                    return $node;
                }

                $text = '';
                foreach ($node['content'] as $childNode) {
                    if (isset($childNode['type']) && 'text' === $childNode['type'] && !empty($childNode['text'])) {
                        $text .= $childNode['text'];
                    }
                }

                if (empty($text)) {
                    return $node;
                }

                $slug = $this->urlSlugGenerator->generateFromString($text);
                if (empty($slug)) {
                    return $node;
                }

                $slug = self::ID_PREFIX_CHAR . $slug;
                $id = $slug;
                $i = 1;
                while (in_array($id, $usedIds)) {
                    $id = sprintf('%s--%s', $slug, $i);
                    $i++;
                }

                $usedIds[] = $id;
                $node['html_id'] = $id;
                return $node;
            },
            $doc['content']
        );

        return $doc;
    }
}
