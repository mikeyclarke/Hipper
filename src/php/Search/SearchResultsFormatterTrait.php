<?php
declare(strict_types=1);

namespace Hipper\Search;

use Hipper\Document\Renderer\HtmlEscaper;

trait SearchResultsFormatterTrait
{
    public function getSnippet(array $result, array $snippetKeys): ?string
    {
        foreach ($snippetKeys as $snippetKey) {
            if (!isset($result[$snippetKey])) {
                continue;
            }

            $snippet = $result[$snippetKey];
            if (empty(trim($snippet)) || false === mb_strpos($snippet, '%ts-mark%')) {
                continue;
            }

            return $this->convertHighlightSyntaxToHtml($snippet);
        }

        return null;
    }

    private function convertHighlightSyntaxToHtml($snippet): string
    {
        $snippet = HtmlEscaper::escapeInnerText($snippet, false);
        $snippet = str_replace('%ts-mark%', '<mark>', $snippet);
        $snippet = str_replace('%/ts-mark%', '</mark>', $snippet);
        return $snippet;
    }
}
