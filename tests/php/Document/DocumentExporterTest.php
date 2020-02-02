<?php
declare(strict_types=1);

namespace Hipper\Tests\Document;

use Hipper\Document\DocumentExporter;
use Hipper\Document\DocumentModel;
use Hipper\Document\DocumentRenderer;
use Hipper\Document\Renderer\Decoder;
use Hipper\Document\Renderer\RendererResult;
use Hipper\File\FileNameGenerator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class DocumentExporterTest extends TestCase
{
    private $decoder;
    private $documentRenderer;
    private $fileNameGenerator;
    private $documentExporter;

    public function setUp(): void
    {
        $this->decoder = m::mock(Decoder::class);
        $this->documentRenderer = m::mock(DocumentRenderer::class);
        $this->fileNameGenerator = m::mock(FileNameGenerator::class);

        $this->documentExporter = new DocumentExporter(
            $this->decoder,
            $this->documentRenderer,
            $this->fileNameGenerator
        );
    }

    /**
     * @test
     */
    public function export()
    {
        $documentName = 'Some doc';
        $documentContent = <<<JSON
{
    "type": "doc",
    "content": [
        {
            "type": "paragraph",
            "content": [
                {
                    "type": "text",
                    "text": "Here is some text."
                }
            ]
        },
        {
            "type": "code",
            "content": [
                {
                    "type": "text",
                    "text": "Here is some more text."
                }
            ]
        }
    ]
}
JSON;

        $document = new DocumentModel;
        $document->setName($documentName);
        $document->setContent($documentContent);
        $organizationDomain = 'acme.usehipper.com';

        $fileName = 'Some doc.md';
        $decoded = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Here is some text.',
                        ],
                    ],
                ],
                [
                    'type' => 'code',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Here is some more text.',
                        ],
                    ],
                ],
            ],
        ];
        $withNameInjected = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Some doc',
                        ],
                    ],
                ],
                [
                    'type' => 'paragraph',
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Here is some text.',
                        ],
                    ],
                ],
                [
                    'type' => 'code',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Here is some more text.',
                        ],
                    ],
                ],
            ],
        ];
        $markdown = "# Some doc\nHere is some text.\nHere is some more text.\n";
        $rendererResult = new RendererResult;
        $rendererResult->setContent($markdown);

        $this->createFileNameGeneratorExpectation([$documentName, 'md', 'Untitled doc'], $fileName);
        $this->createDecoderExpectation([$documentContent], $decoded);
        $this->createDocumentRendererExpectation([$withNameInjected, 'markdown', $organizationDomain], $rendererResult);

        $result = $this->documentExporter->export($document, $organizationDomain);
        $this->assertIsArray($result);
        $this->assertEquals($markdown, $result[0]);
        $this->assertEquals($fileName, $result[1]);
    }

    private function createDocumentRendererExpectation($args, $result)
    {
        $this->documentRenderer
            ->shouldReceive('render')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createDecoderExpectation($args, $result)
    {
        $this->decoder
            ->shouldReceive('decode')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createFileNameGeneratorExpectation($args, $result)
    {
        $this->fileNameGenerator
            ->shouldReceive('generateFromString')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
