<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer;

use Hipper\Document\Renderer\HtmlEscaper;
use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\HtmlFragmentRendererContextFactory;
use Hipper\Document\Renderer\HtmlRenderer;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class HtmlRendererTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $contextFactory;
    private $organizationDomain;
    private $context;
    private $htmlEscaper;

    public function setUp(): void
    {
        $this->contextFactory = m::mock(HtmlFragmentRendererContextFactory::class);
        $this->organizationDomain = 'acme.usehipper.com';
        $this->context = m::mock(HtmlFragmentRendererContext::class);
        $this->htmlEscaper = m::mock(HtmlEscaper::class);
    }

    /**
     * @test
     */
    public function invalidJsonReturnsEmptyString()
    {
        $doc = '*** not JSON ***';

        $htmlRenderer = new HtmlRenderer(
            $this->contextFactory,
            ['allowed-marks'],
            ['allowed-nodes']
        );

        $expected = '';

        $result = $htmlRenderer->render($doc, $this->organizationDomain);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function docNotTypeDocReturnsEmptyString()
    {
        $doc = '{"type": "paragraph", "content": [{"type": "text", "text": "Hello"}]}';

        $htmlRenderer = new HtmlRenderer(
            $this->contextFactory,
            ['allowed-marks'],
            ['allowed-nodes']
        );

        $expected = '';

        $result = $htmlRenderer->render($doc, $this->organizationDomain);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function docWithNoContentReturnsEmptyString()
    {
        $doc = '{"type": "doc"}';

        $htmlRenderer = new HtmlRenderer(
            $this->contextFactory,
            ['allowed-marks'],
            ['allowed-nodes']
        );

        $expected = '';

        $result = $htmlRenderer->render($doc, $this->organizationDomain);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function nodesWithoutATypeAreSkipped()
    {
        $doc = '{"type": "doc", "content": [{"text": "Foo"}, {"content": [{"type": "horizontal_rule"}]}]}';

        $htmlRenderer = new HtmlRenderer(
            $this->contextFactory,
            [],
            ['horizontal_rule']
        );

        $this->createContextFactoryExpectation();

        $expected = '';

        $result = $htmlRenderer->render($doc, $this->organizationDomain);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function disallowedNodesAreSkipped()
    {
        $doc = '{"type": "doc", "content": [{"type": "horizontal_rule"}]}';

        $htmlRenderer = new HtmlRenderer(
            $this->contextFactory,
            [],
            ['paragraph']
        );

        $this->createContextFactoryExpectation();

        $expected = '';

        $result = $htmlRenderer->render($doc, $this->organizationDomain);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function textNodeTextIsEscaped()
    {
        $doc = '{"type": "doc", "content": [{"type": "paragraph", "content": [{"type": "text", "text": "Hi!!!"}]}]}';

        $htmlRenderer = new HtmlRenderer(
            $this->contextFactory,
            [],
            ['paragraph', 'text']
        );

        $this->createContextFactoryExpectation();
        $this->createContextGetHtmlEscaperExpectation();
        $this->createHtmlEscaperExpectation(['Hi!!!'], 'Hi I’m escaped!!!');

        $expected = '<p>Hi I’m escaped!!!</p>';

        $result = $htmlRenderer->render($doc, $this->organizationDomain);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function nodesThatReturnNullHtmlTagsAreSkipped()
    {
        // Image node returns null for HTML tags if no attributes with a valid `src`
        $doc = '{"type": "doc", "content": [{"type": "image"}, {"type": "horizontal_rule"}]}';

        $htmlRenderer = new HtmlRenderer(
            $this->contextFactory,
            [],
            ['image', 'horizontal_rule']
        );

        $this->createContextFactoryExpectation();

        $expected = '<hr>';

        $result = $htmlRenderer->render($doc, $this->organizationDomain);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function emptyParagraphsDynamicallyAddAHardBreakChildNode()
    {
        $doc = '{"type": "doc", "content": [{"type": "paragraph"}]}';

        $htmlRenderer = new HtmlRenderer(
            $this->contextFactory,
            [],
            ['paragraph', 'hard_break']
        );

        $this->createContextFactoryExpectation();

        $expected = '<p><br></p>';

        $result = $htmlRenderer->render($doc, $this->organizationDomain);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function disallowedMarksAreSkipped()
    {
        $doc = '
            {
                "type": "doc",
                "content": [
                    {
                        "type": "paragraph",
                        "content": [
                            {
                                "type": "text",
                                "text": "We are "
                            },
                            {
                                "type": "text",
                                "text": "bold",
                                "marks": [
                                    {
                                        "type": "strong"
                                    }
                                ]
                            },
                            {
                                "type": "text",
                                "text": " and "
                            },
                            {
                                "type": "text",
                                "text": "italic",
                                "marks": [
                                    {
                                        "type": "emphasis"
                                    }
                                ]
                            },
                            {
                                "type": "text",
                                "text": "."
                            }
                        ]
                    }
                ]
            }
        ';

        $htmlRenderer = new HtmlRenderer(
            $this->contextFactory,
            ['emphasis'],
            ['paragraph', 'text']
        );

        $this->createContextFactoryExpectation();
        $this->createContextGetHtmlEscaperExpectation();
        $this->createHtmlEscaperExpectation(['We are '], 'We are ');
        $this->createContextGetHtmlEscaperExpectation();
        $this->createHtmlEscaperExpectation(['bold'], 'bold');
        $this->createContextGetHtmlEscaperExpectation();
        $this->createHtmlEscaperExpectation([' and '], ' and ');
        $this->createContextGetHtmlEscaperExpectation();
        $this->createHtmlEscaperExpectation(['italic'], 'italic');
        $this->createContextGetHtmlEscaperExpectation();
        $this->createHtmlEscaperExpectation(['.'], '.');

        $expected = '<p>We are bold and <em>italic</em>.</p>';

        $result = $htmlRenderer->render($doc, $this->organizationDomain);
        $this->assertEquals($expected, $result);
    }

    private function createHtmlEscaperExpectation($args, $result)
    {
        $this->htmlEscaper
            ->shouldReceive('escapeInnerText')
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

    private function createContextFactoryExpectation()
    {
        $this->contextFactory
            ->shouldReceive('create')
            ->once()
            ->with($this->organizationDomain)
            ->andReturn($this->context);
    }
}
