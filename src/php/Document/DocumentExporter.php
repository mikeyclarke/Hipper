<?php
declare(strict_types=1);

namespace Hipper\Document;

use Hipper\Document\DocumentModel;
use Hipper\Document\DocumentRenderer;
use Hipper\Document\Renderer\Decoder;
use Hipper\Document\Renderer\Exception\ContentDecodeException;
use Hipper\File\FileNameGenerator;

class DocumentExporter
{
    private const DEFAULT_FILE_NAME = 'Untitled doc';

    private Decoder $decoder;
    private DocumentRenderer $documentRenderer;
    private FileNameGenerator $fileNameGenerator;

    public function __construct(
        Decoder $decoder,
        DocumentRenderer $documentRenderer,
        FileNameGenerator $fileNameGenerator
    ) {
        $this->decoder = $decoder;
        $this->documentRenderer = $documentRenderer;
        $this->fileNameGenerator = $fileNameGenerator;
    }

    public function export(DocumentModel $document, string $organizationDomain): array
    {
        $fileName = $this->fileNameGenerator->generateFromString($document->getName(), 'md', self::DEFAULT_FILE_NAME);

        try {
            $decoded = $this->decoder->decode($document->getContent());
        } catch (ContentDecodeException $e) {
            return ['', $fileName];
        }

        $this->injectName($document->getName(), $decoded);
        $rendererResult = $this->documentRenderer->render($decoded, 'markdown', $organizationDomain);

        return [$rendererResult->getContent(), $fileName];
    }

    private function injectName(string $name, array &$docContent): void
    {
        $nodes = [
            [
                'type' => 'heading',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $name,
                    ],
                ],
            ],
        ];

        array_unshift($docContent['content'], ...$nodes);
    }
}
