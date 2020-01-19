<?php
declare(strict_types=1);

namespace Hipper\Tests\Search;

use Hipper\Document\Renderer\HtmlEscaper;
use Hipper\Search\SearchResultsFormatterTrait;
use PHPUnit\Framework\TestCase;

class SearchResultsFormatterTraitTest extends TestCase
{
    private $trait;

    public function setUp(): void
    {
        $this->trait = $this->getMockForTrait(SearchResultsFormatterTrait::class);
    }

    /**
     * @test
     */
    public function firstSnippetContainingHighlightsIsUsed()
    {
        $snippetKeys = ['bar_snippet', 'foo_snippet', 'baz_snippet'];
        $searchResult = [
            'bar_snippet' => 'I am bar.',
            'foo_snippet' => 'I am %ts-mark%foo%/ts-mark%.',
            'baz_snippet' => 'I am %ts-mark%baz%/ts-mark%.',
        ];

        $expected = 'I am <mark>foo</mark>.';

        $result = $this->trait->getSnippet($searchResult, $snippetKeys);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function noSnippetsWithHighlightsReturnsNull()
    {
        $snippetKeys = ['bar_snippet', 'foo_snippet', 'baz_snippet'];
        $searchResult = [
            'bar_snippet' => 'I am bar.',
            'foo_snippet' => 'I am foo.',
            'baz_snippet' => 'I am baz.',
        ];

        $expected = null;

        $result = $this->trait->getSnippet($searchResult, $snippetKeys);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function snippetIsEscaped()
    {
        $snippetKeys = ['snippet'];
        $searchResult = [
            'snippet' => '<em>I</em> am a <strong>lovely</strong> %ts-mark%snippet%/ts-mark%.',
        ];

        $expected =
            HtmlEscaper::escapeInnerText('<em>I</em> am a <strong>lovely</strong> ', false) . '<mark>snippet</mark>.';

        $result = $this->trait->getSnippet($searchResult, $snippetKeys);
        $this->assertEquals($expected, $result);
    }
}
