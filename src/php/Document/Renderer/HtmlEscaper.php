<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer;

class HtmlEscaper
{
    public static function escapeInnerText(string $text, bool $doubleEncode = true): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', $doubleEncode);
    }

    public static function escapeAttributeValue($value): string
    {
        if (is_bool($value)) {
            return ($value) ? 'true' : 'false';
        }

        if (is_int($value) || is_float($value)) {
            $value = (string) $value;
            return $value;
        }

        if (!is_string($value)) {
            return '';
        }

        if (!preg_match('//u', $value)) {
            return ''; // Not valid UTF-8
        }

        $result = preg_replace_callback('#[^a-zA-Z0-9,\.\-_]#Su', function ($matches) {
            /**
            * This function is adapted from Twig which is itself adapted from code coming from Zend Framework.
            *
            * @copyright (c) Fabien Potencier
            * @license   https://github.com/twigphp/Twig/blob/2.x/LICENSE
            *
            * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (https://www.zend.com)
            * @license   https://framework.zend.com/license/new-bsd New BSD License
            */
            $chr = $matches[0];
            $ord = \ord($chr);

            /*
            * The following replaces characters undefined in HTML with the
            * hex entity for the Unicode replacement character.
            */
            if (($ord <= 0x1f && "\t" != $chr && "\n" != $chr && "\r" != $chr) || ($ord >= 0x7f && $ord <= 0x9f)) {
                return '&#xFFFD;';
            }

            /*
            * Check if the current character to escape has a name entity we should
            * replace it with while grabbing the hex value of the character.
            */
            if (1 === \strlen($chr)) {
                /*
                * While HTML supports far more named entities, the lowest common denominator
                * has become HTML5's XML Serialisation which is restricted to the those named
                * entities that XML supports. Using HTML entities would result in this error:
                *     XML Parsing Error: undefined entity
                */
                static $entityMap = [
                34 => '&quot;', /* quotation mark */
                38 => '&amp;',  /* ampersand */
                60 => '&lt;',   /* less-than sign */
                62 => '&gt;',   /* greater-than sign */
                ];

                if (isset($entityMap[$ord])) {
                    return $entityMap[$ord];
                }

                return sprintf('&#x%02X;', $ord);
            }

            /*
            * Per OWASP recommendations, we'll use hex entities for any other
            * characters where a named entity does not exist.
            */
            return sprintf('&#x%04X;', mb_ord($chr, 'UTF-8'));
        }, $value);

        return $result;
    }
}
