<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\Node\Heading;
use Hipper\Document\Renderer\StringTerminator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class HeadingTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $context;
    private $headingNode;
    private $stringTerminator;

    public function setUp(): void
    {
        $this->context = m::mock(HtmlFragmentRendererContext::class);
        $this->headingNode = new Heading($this->context);

        $this->stringTerminator = m::mock(StringTerminator::class);
    }

    /**
     * @test
     */
    public function defaultsToH1()
    {
        $expected = ['<h1>', '</h1>'];

        $result = $this->headingNode->getHtmlTags(null, null);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function headingLevelUsedFromAttributes()
    {
        $attributes = ['level' => 4];
        $htmlId = null;

        $expected = ['<h4>', '</h4>'];

        $result = $this->headingNode->getHtmlTags($attributes, $htmlId);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function nonIntegerLevelAttributeValueIsDiscarded()
    {
        $attributes = ['level' => '1></h1><script>window.location.assign("http://scammy-site.example.com");</script>'];
        $htmlId = null;

        $expected = ['<h1>', '</h1>'];

        $result = $this->headingNode->getHtmlTags($attributes, $htmlId);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function htmlIdIsOutput()
    {
        $attributes = null;
        $htmlId = 'my-heading';

        $expected = ['<h1 id="my-heading">', '</h1>'];

        $result = $this->headingNode->getHtmlTags($attributes, $htmlId);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function toPlainTextString()
    {
        $textContent = 'Speaking English is exhausting';

        $terminated = 'Speaking English is exhausting.';

        $this->createHtmlFragmentRendererContextExpectation();
        $this->createStringTerminatorExpectation([$textContent], $terminated);

        $expected = $terminated . "\r\n";

        $result = $this->headingNode->toPlainTextString($textContent);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function toMarkdownString()
    {
        $content = 'Speaking English is exhausting';
        $attributes = ['level' => 4];

        $expected = "#### Speaking English is exhausting\n\n";

        $result = $this->headingNode->toMarkdownString($content, 0, null, $attributes);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function toMarkdownStringWithDefault()
    {
        $content = 'Speaking English is exhausting';
        $attributes = null;

        $expected = "# Speaking English is exhausting\n\n";

        $result = $this->headingNode->toMarkdownString($content, 0, null, $attributes);
        $this->assertEquals($expected, $result);
    }

    private function createStringTerminatorExpectation($args, $result)
    {
        $this->stringTerminator
            ->shouldReceive('terminateStringWithPunctuationCharacter')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createHtmlFragmentRendererContextExpectation()
    {
        $this->context
            ->shouldReceive('getStringTerminator')
            ->once()
            ->andReturn($this->stringTerminator);
    }
}
