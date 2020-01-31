<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer;

class MarkdownEscaper
{
    public static function escapeInnerText(string $text, bool $doubleEncode = true): string
    {
        $result = $text;
        $result = preg_replace('~\s+~u', ' ', $result);
        $result = preg_replace('~([*_\\[\\]\\\\])~u', '\\\\$1', $result);
        $result = preg_replace('~^#~u', '\\\\#', $result);
        $result = htmlspecialchars($result, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8', $doubleEncode);

        return $result;
    }
}
