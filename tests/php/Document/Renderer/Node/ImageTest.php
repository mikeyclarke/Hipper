<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlEscaper;
use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\Node\Image;
use Hipper\Document\Renderer\UrlAttributeValidator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $context;
    private $imageNode;
    private $urlAttributeValidator;
    private $htmlEscaper;

    public function setUp(): void
    {
        $this->context = m::mock(HtmlFragmentRendererContext::class);
        $this->imageNode = new Image(
            $this->context
        );

        $this->urlAttributeValidator = m::mock(UrlAttributeValidator::class);
        $this->htmlEscaper = m::mock(HtmlEscaper::class);
    }

    /**
     * @test
     */
    public function noHtmlTagsReturnedIfNoSrcAttribute()
    {
        $attributes = ['title' => 'Foo'];
        $htmlId = null;

        $result = $this->imageNode->getHtmlTags($attributes, $htmlId);
        $this->assertEquals(null, $result);
    }

    /**
     * @test
     */
    public function noHtmlTagsReturnedIfSrcDoesNotValidateAsUrl()
    {
        $attributes = ['src' => 'unsafe-url'];
        $htmlId = null;

        $this->createContextGetUrlAttributeValidatorExpectation();
        $this->createUrlAttributeValidatorExpectation([$attributes['src']], false);

        $result = $this->imageNode->getHtmlTags($attributes, $htmlId);
        $this->assertEquals(null, $result);
    }

    /**
     * @test
     */
    public function supportedAttributesAreEscapedAndOutput()
    {
        $attributes = [
            'alt' => 'An image containing a thingy',
            'src' => 'https://objects.usehipper.com/0p/4g/0pksd560-09dshq65ns-c89yd3hiu-3gbc87eu8.svg',
            'title' => 'Thingy',
        ];
        $htmlId = null;

        $this->createContextGetUrlAttributeValidatorExpectation();
        $this->createUrlAttributeValidatorExpectation([$attributes['src']], true);
        $this->createContextGetHtmlEscaperExpectation();
        $this->createHtmlEscaperExpectation([$attributes['alt']], $attributes['alt']);
        $this->createHtmlEscaperExpectation([$attributes['src']], $attributes['src']);
        $this->createHtmlEscaperExpectation([$attributes['title']], $attributes['title']);

        $expected = [
            sprintf(
                '<img alt="%s" src="%s" title="%s">',
                $attributes['alt'],
                $attributes['src'],
                $attributes['title']
            )
        ];

        $result = $this->imageNode->getHtmlTags($attributes, $htmlId);
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
