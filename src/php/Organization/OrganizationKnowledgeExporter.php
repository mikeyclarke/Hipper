<?php

declare(strict_types=1);

namespace Hipper\Organization;

use Hipper\File\FileNameGenerator;
use Hipper\Filesystem\FilesystemFactory;
use Hipper\Knowledgebase\KnowledgebaseExporter;
use Hipper\Knowledgebase\KnowledgebaseModel;
use Hipper\Organization\OrganizationModel;
use Hipper\Project\ProjectRepository;
use Hipper\Team\TeamRepository;
use Hipper\ZipArchive\ZipArchiver;
use Symfony\Component\Filesystem\Filesystem;

class OrganizationKnowledgeExporter
{
    private FileNameGenerator $fileNameGenerator;
    private FilesystemFactory $filesystemFactory;
    private KnowledgebaseExporter $knowledgebaseExporter;
    private ProjectRepository $projectRepository;
    private TeamRepository $teamRepository;
    private ZipArchiver $zipArchiver;
    private string $environmentDomain;

    public function __construct(
        FileNameGenerator $fileNameGenerator,
        FilesystemFactory $filesystemFactory,
        KnowledgebaseExporter $knowledgebaseExporter,
        ProjectRepository $projectRepository,
        TeamRepository $teamRepository,
        ZipArchiver $zipArchiver,
        string $environmentDomain
    ) {
        $this->fileNameGenerator = $fileNameGenerator;
        $this->filesystemFactory = $filesystemFactory;
        $this->knowledgebaseExporter = $knowledgebaseExporter;
        $this->projectRepository = $projectRepository;
        $this->teamRepository = $teamRepository;
        $this->zipArchiver = $zipArchiver;
        $this->environmentDomain = $environmentDomain;
    }

    public function export(OrganizationModel $organization): string
    {
        $organizationId = $organization->getId();
        $organizationDomain = sprintf('%s.%s', $organization->getSubdomain(), $this->environmentDomain);

        $filesystem = $this->filesystemFactory->create();

        $directory = sprintf('%s/%s', sys_get_temp_dir(), $this->fileNameGenerator->generateRandom());
        $filesystem->mkdir($directory);

        $this->exportOrganization($filesystem, $directory, $organization, $organizationDomain);

        $teams = $this->teamRepository->getAll($organizationId);
        foreach ($teams as $team) {
            $this->exportTeam($filesystem, $directory, $organizationId, $organizationDomain, $team);
        }

        $projects = $this->projectRepository->getAll($organizationId);
        foreach ($projects as $project) {
            $this->exportProject($filesystem, $directory, $organizationId, $organizationDomain, $project);
        }

        $zipPathname = $this->createZip($directory);

        $filesystem->remove($directory);

        return $zipPathname;
    }

    private function createDirectory(Filesystem $filesystem, string $parentDir, string $name): string
    {
        $directoryPathname = sprintf('%s/%s', $parentDir, $this->fileNameGenerator->generateFromString($name));
        $filesystem->mkdir($directoryPathname);
        return $directoryPathname;
    }

    private function addKnowledgebase(
        string $knowledgebaseId,
        string $organizationId,
        string $organizationDomain,
        string $directory
    ): void {
        $knowledgebase = KnowledgebaseModel::createFromArray([
            'id' => $knowledgebaseId,
            'organization_id' => $organizationId,
        ]);
        $this->knowledgebaseExporter->export($knowledgebase, $organizationDomain, $directory);
    }

    private function exportOrganization(
        Filesystem $filesystem,
        string $parentDir,
        OrganizationModel $organization,
        string $organizationDomain
    ): void {
        $orgDir = $this->createDirectory($filesystem, $parentDir, sprintf('%s docs', $organization->getName()));
        $this->addKnowledgebase(
            $organization->getKnowledgebaseId(),
            $organization->getId(),
            $organizationDomain,
            $orgDir
        );
    }

    private function exportTeam(
        Filesystem $filesystem,
        string $parentDir,
        string $organizationId,
        string $organizationDomain,
        array $team
    ): void {
        $teamDir = $this->createDirectory($filesystem, $parentDir, sprintf('%s team docs', $team['name']));
        $this->addKnowledgebase($team['knowledgebase_id'], $organizationId, $organizationDomain, $teamDir);
    }

    private function exportProject(
        Filesystem $filesystem,
        string $parentDir,
        string $organizationId,
        string $organizationDomain,
        array $project
    ): void {
        $projectDir = $this->createDirectory($filesystem, $parentDir, sprintf('%s project docs', $project['name']));
        $this->addKnowledgebase($project['knowledgebase_id'], $organizationId, $organizationDomain, $projectDir);
    }

    private function createZip(string $directoryPathname): string
    {
        $zipPathname = sprintf('%s/%s', sys_get_temp_dir(), $this->fileNameGenerator->generateRandom('zip'));
        $this->zipArchiver->recursivelyArchiveDirectory($zipPathname, $directoryPathname);
        return $zipPathname;
    }
}
