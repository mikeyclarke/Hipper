<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer;

use Hipper\Document\Renderer\HtmlEscaper;
use PHPUnit\Framework\TestCase;

class HtmlEscaperTest extends TestCase
{
    private $htmlEscaper;

    public function setUp(): void
    {
        $this->htmlEscaper = new HtmlEscaper;
    }

    /**
     * @test
     */
    public function escapeInnerText()
    {
        $text = '
            // A comment
            <p>A code example that needs escaping</p>
        ';

        $expected = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $result = $this->htmlEscaper->escapeInnerText($text);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function escapeAttributeValueWithBoolean()
    {
        $value = true;

        $expected = 'true';

        $result = $this->htmlEscaper->escapeAttributeValue($value);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function escapeAttributeValueWithInt()
    {
        $value = 300;

        $expected = '300';

        $result = $this->htmlEscaper->escapeAttributeValue($value);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function escapeAttributeValueWithFloat()
    {
        $value = 300.300;

        $expected = '300.3';

        $result = $this->htmlEscaper->escapeAttributeValue($value);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function escapeAttributeValueEscapesOwaspRecommendedRanges()
    {
        function codepointToUtf8($codepoint)
        {
            if ($codepoint < 0x80) {
                return \chr($codepoint);
            }
            if ($codepoint < 0x800) {
                return \chr($codepoint >> 6 & 0x3f | 0xc0)
                .\chr($codepoint & 0x3f | 0x80);
            }
            if ($codepoint < 0x10000) {
                return \chr($codepoint >> 12 & 0x0f | 0xe0)
                    .\chr($codepoint >> 6 & 0x3f | 0x80)
                    .\chr($codepoint & 0x3f | 0x80);
            }
            if ($codepoint < 0x110000) {
                return \chr($codepoint >> 18 & 0x07 | 0xf0)
                    .\chr($codepoint >> 12 & 0x3f | 0x80)
                    .\chr($codepoint >> 6 & 0x3f | 0x80)
                    .\chr($codepoint & 0x3f | 0x80);
            }
            throw new \Exception('Codepoint requested outside of Unicode range.');
        }

        $immune = [',', '.', '-', '_']; // Exceptions to escaping ranges
        for ($chr = 0; $chr < 0xFF; ++$chr) {
            if ($chr >= 0x30 && $chr <= 0x39
            || $chr >= 0x41 && $chr <= 0x5A
            || $chr >= 0x61 && $chr <= 0x7A) {
                $literal = codepointToUtf8($chr);
                $result = $this->htmlEscaper->escapeAttributeValue($literal);
                $this->assertEquals($literal, $result);
            } else {
                $literal = codepointToUtf8($chr);
                if (\in_array($literal, $immune)) {
                    $result = $this->htmlEscaper->escapeAttributeValue($literal);
                    $this->assertEquals($literal, $result);
                } else {
                    $result = $this->htmlEscaper->escapeAttributeValue($literal);
                    $this->assertNotEquals($literal, $result, "$literal should be escaped!");
                }
            }
        }
    }

    /**
     * @test
     * @dataProvider htmlSpecialCharacterProvider
     */
    public function escapeAttributeValueEscapesSpecialCharacters($character, $replacement)
    {
        $result = $this->htmlEscaper->escapeAttributeValue($character);
        $this->assertEquals($replacement, $result);
    }

    public function htmlSpecialCharacterProvider()
    {
        return [
            ['\'', '&#x27;'],
            ['Ā', '&#x0100;'],
            ['😀', '&#x1F600;'],
            [',', ','],
            ['.', '.'],
            ['-', '-'],
            ['_', '_'],
            ['a', 'a'],
            ['A', 'A'],
            ['z', 'z'],
            ['Z', 'Z'],
            ['0', '0'],
            ['9', '9'],
            ["\r", '&#x0D;'],
            ["\n", '&#x0A;'],
            ["\t", '&#x09;'],
            ["\0", '&#xFFFD;'],
            ['<', '&lt;'],
            ['>', '&gt;'],
            ['&', '&amp;'],
            ['"', '&quot;'],
            [' ', '&#x20;'],
        ];
    }
}
