<?php

declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Hipper\Document\DocumentExporter;
use Hipper\Document\DocumentModel;
use Hipper\File\FileNameGenerator;
use Hipper\Filesystem\FilesystemFactory;
use Hipper\Knowledgebase\KnowledgebaseRepository;
use Symfony\Component\Filesystem\Filesystem;

class KnowledgebaseExporter
{
    private DocumentExporter $documentExporter;
    private FileNameGenerator $fileNameGenerator;
    private FilesystemFactory $filesystemFactory;
    private KnowledgebaseRepository $knowledgebaseRepository;

    public function __construct(
        DocumentExporter $documentExporter,
        FileNameGenerator $fileNameGenerator,
        FilesystemFactory $filesystemFactory,
        KnowledgebaseRepository $knowledgebaseRepository
    ) {
        $this->documentExporter = $documentExporter;
        $this->fileNameGenerator = $fileNameGenerator;
        $this->filesystemFactory = $filesystemFactory;
        $this->knowledgebaseRepository = $knowledgebaseRepository;
    }

    public function export(
        KnowledgebaseModel $knowledgebase,
        string $organizationDomain,
        string $directoryPathname = null
    ): string {
        $filesystem = $this->filesystemFactory->create();

        if (null === $directoryPathname) {
            $directoryPathname = sys_get_temp_dir();
        }

        $contents = $this->knowledgebaseRepository->getContents(
            $knowledgebase->getId(),
            $knowledgebase->getOrganizationId()
        );

        $tree = $this->createTree($contents);
        foreach ($tree as $entry) {
            $this->writeToDirectory($filesystem, $entry, $directoryPathname, $organizationDomain);
        }

        return $directoryPathname;
    }

    private function createTree(array $rows): array
    {
        $result = array_filter(
            $rows,
            function ($row) {
                return null === $row['parent_topic_id'];
            }
        );

        foreach ($result as &$topLevel) {
            if ($topLevel['type'] === 'topic') {
                $topLevel['children'] = $this->getChildren($topLevel['id'], $rows);
            }
        }

        return $result;
    }

    private function getChildren(string $topicId, array $rows): array
    {
        $result = array_filter(
            $rows,
            function ($row) use ($topicId) {
                return $row['parent_topic_id'] === $topicId;
            }
        );

        foreach ($result as &$child) {
            if ($child['type'] === 'topic') {
                $child['children'] = $this->getChildren($child['id'], $rows);
            }
        }

        return $result;
    }

    private function writeToDirectory(
        Filesystem $filesystem,
        array $node,
        string $directory,
        string $organizationDomain
    ): void {
        if ($node['type'] === 'document') {
            $model = DocumentModel::createFromArray($node);
            list($content, $fileName) = $this->documentExporter->export($model, $organizationDomain);
            $filesystem->dumpFile($directory . '/' . $fileName, $content);
            return;
        }

        $topicDirectory = $directory . '/' . $this->fileNameGenerator->generateFromString($node['name']);
        $filesystem->mkdir($topicDirectory);

        if (isset($node['children'])) {
            foreach ($node['children'] as $childNode) {
                $this->writeToDirectory($filesystem, $childNode, $topicDirectory, $organizationDomain);
            }
        }
    }
}
