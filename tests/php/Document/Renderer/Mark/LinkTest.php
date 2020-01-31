<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer\Mark;

use Hipper\Document\Renderer\HtmlEscaper;
use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\Mark\Link;
use Hipper\Document\Renderer\UrlAttributeValidator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class LinkTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $context;
    private $linkMark;
    private $htmlEscaper;
    private $urlAttributeValidator;

    public function setUp(): void
    {
        $this->context = m::mock(HtmlFragmentRendererContext::class);
        $this->linkMark = new Link(
            $this->context
        );

        $this->htmlEscaper = m::mock(HtmlEscaper::class);
        $this->urlAttributeValidator = m::mock(UrlAttributeValidator::class);
    }

    /**
     * @test
     */
    public function noHtmlTagsReturnedIfNoHrefAttribute()
    {
        $attributes = ['title' => 'Foo'];

        $result = $this->linkMark->getHtmlTags($attributes);
        $this->assertEquals(null, $result);
    }

    /**
     * @test
     */
    public function noHtmlTagsReturnedIfHrefIsNotValidUrl()
    {
        $attributes = ['href' => 'unsafe-url'];

        $this->createContextGetUrlAttributeValidatorExpectation();
        $this->createUrlAttributeValidatorExpectation([$attributes['href']], false);

        $result = $this->linkMark->getHtmlTags($attributes);
        $this->assertEquals(null, $result);
    }

    /**
     * @test
     */
    public function noopenerAndNoreferrerAreNotAppliedForInternalLinks()
    {
        $attributes = ['href' => 'https://acme.usehipper.com/'];

        $this->createContextGetUrlAttributeValidatorExpectation();
        $this->createUrlAttributeValidatorExpectation([$attributes['href']], true);
        $this->createContextGetOrganizationDomainExpectation('acme.usehipper.com');
        $this->createContextGetHtmlEscaperExpectation();
        $this->createHtmlEscaperExpectation(
            [$attributes['href']],
            'https&#x3A;&#x2F;&#x2F;acme.usehipper.com&#x2F;'
        );

        $expected = ['<a href="https&#x3A;&#x2F;&#x2F;acme.usehipper.com&#x2F;">', '</a>'];

        $result = $this->linkMark->getHtmlTags($attributes);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function titleAttributeOutput()
    {
        $attributes = [
            'href' => 'https://example.com',
            'title' => 'Foo',
        ];

        $this->createContextGetUrlAttributeValidatorExpectation();
        $this->createUrlAttributeValidatorExpectation([$attributes['href']], true);
        $this->createContextGetOrganizationDomainExpectation('acme.usehipper.com');
        $this->createContextGetHtmlEscaperExpectation();
        $this->createHtmlEscaperExpectation([$attributes['href']], 'https&#x3A;&#x2F;&#x2F;example.com');
        $this->createHtmlEscaperExpectation([$attributes['title']], $attributes['title']);

        $expected = ['<a rel="noopener noreferrer" href="https&#x3A;&#x2F;&#x2F;example.com" title="Foo">', '</a>'];

        $result = $this->linkMark->getHtmlTags($attributes);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function spellcheckAttributeNotOutputIfNotTrue()
    {
        $attributes = [
            'href' => 'https://example.com',
            'spellcheck' => false,
        ];

        $this->createContextGetUrlAttributeValidatorExpectation();
        $this->createUrlAttributeValidatorExpectation([$attributes['href']], true);
        $this->createContextGetOrganizationDomainExpectation('acme.usehipper.com');
        $this->createContextGetHtmlEscaperExpectation();
        $this->createHtmlEscaperExpectation([$attributes['href']], 'https&#x3A;&#x2F;&#x2F;example.com');

        $expected = ['<a rel="noopener noreferrer" href="https&#x3A;&#x2F;&#x2F;example.com">', '</a>'];

        $result = $this->linkMark->getHtmlTags($attributes);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function spellcheckAttributeOutputIfBoolean()
    {
        $attributes = [
            'href' => 'https://usehipper.com',
            'spellcheck' => true,
        ];

        $this->createContextGetUrlAttributeValidatorExpectation();
        $this->createUrlAttributeValidatorExpectation([$attributes['href']], true);
        $this->createContextGetOrganizationDomainExpectation('acme.usehipper.com');
        $this->createContextGetHtmlEscaperExpectation();
        $this->createHtmlEscaperExpectation([$attributes['href']], 'https&#x3A;&#x2F;&#x2F;example.com');

        $expected = [
            '<a rel="noopener noreferrer" href="https&#x3A;&#x2F;&#x2F;example.com" spellcheck="true">',
            '</a>'
        ];

        $result = $this->linkMark->getHtmlTags($attributes);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function originalTextReturnedAsMarkdownIfNoHrefAttribute()
    {
        $text = 'Some text';
        $attributes = [];

        $result = $this->linkMark->toMarkdownString($text, $attributes);
        $this->assertEquals($text, $result);
    }

    /**
     * @test
     */
    public function originalTextReturnedAsMarkdownIfHrefIsNotValidUrl()
    {
        $text = 'Some text';
        $attributes = ['href' => 'unsafe-url'];

        $this->createContextGetUrlAttributeValidatorExpectation();
        $this->createUrlAttributeValidatorExpectation([$attributes['href']], false);

        $result = $this->linkMark->toMarkdownString($text, $attributes);
        $this->assertEquals($text, $result);
    }

    /**
     * @test
     */
    public function toMarkdownString()
    {
        $text = 'Some text';
        $attributes = ['href' => 'https://duckduckgo.com'];

        $this->createContextGetUrlAttributeValidatorExpectation();
        $this->createUrlAttributeValidatorExpectation([$attributes['href']], true);

        $expected = "[Some text](https://duckduckgo.com)";

        $result = $this->linkMark->toMarkdownString($text, $attributes);
        $this->assertEquals($expected, $result);
    }

    private function createHtmlEscaperExpectation($args, $result)
    {
        $this->htmlEscaper
            ->shouldReceive('escapeAttributeValue')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createContextGetHtmlEscaperExpectation()
    {
        $this->context
            ->shouldReceive('getHtmlEscaper')
            ->once()
            ->andReturn($this->htmlEscaper);
    }

    private function createContextGetOrganizationDomainExpectation($result)
    {
        $this->context
            ->shouldReceive('getOrganizationDomain')
            ->once()
            ->andReturn($result);
    }

    private function createUrlAttributeValidatorExpectation($args, $result)
    {
        $this->urlAttributeValidator
            ->shouldReceive('isValid')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createContextGetUrlAttributeValidatorExpectation()
    {
        $this->context
            ->shouldReceive('getUrlAttributeValidator')
            ->once()
            ->andReturn($this->urlAttributeValidator);
    }
}
