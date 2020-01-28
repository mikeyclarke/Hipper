<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer;

class StringTerminator
{
    public function terminateStringWithPunctuationCharacter(string $str, string $character = '.'): string
    {
        $endTrimmed = rtrim($str);
        $endsInPunctuation = preg_match('/[\p{P}]$/u', mb_substr($endTrimmed, -1));

        if ($endsInPunctuation) {
            return $str;
        }

        return $endTrimmed . $character;
    }
}
