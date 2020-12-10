<?php

declare(strict_types=1);

namespace Hipper\Tests\Organization;

use Hipper\File\FileNameGenerator;
use Hipper\Filesystem\FilesystemFactory;
use Hipper\Knowledgebase\KnowledgebaseExporter;
use Hipper\Knowledgebase\KnowledgebaseModel;
use Hipper\Organization\OrganizationKnowledgeExporter;
use Hipper\Organization\OrganizationModel;
use Hipper\Project\ProjectRepository;
use Hipper\Team\TeamRepository;
use Hipper\ZipArchive\ZipArchiver;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class OrganizationKnowledgeExporterTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $fileNameGenerator;
    private $filesystemFactory;
    private $knowledgebaseExporter;
    private $projectRepository;
    private $teamRepository;
    private $zipArchiver;
    private $environmentDomain;
    private $organizationKnowledgeExporter;
    private $filesystem;

    public function setUp(): void
    {
        $this->fileNameGenerator = m::mock(FileNameGenerator::class);
        $this->filesystemFactory = m::mock(FilesystemFactory::class);
        $this->knowledgebaseExporter = m::mock(KnowledgebaseExporter::class);
        $this->projectRepository = m::mock(ProjectRepository::class);
        $this->teamRepository = m::mock(TeamRepository::class);
        $this->zipArchiver = m::mock(ZipArchiver::class);
        $this->environmentDomain = 'usehipper.test';

        $this->organizationKnowledgeExporter = new OrganizationKnowledgeExporter(
            $this->fileNameGenerator,
            $this->filesystemFactory,
            $this->knowledgebaseExporter,
            $this->projectRepository,
            $this->teamRepository,
            $this->zipArchiver,
            $this->environmentDomain
        );

        $this->filesystem = m::mock(Filesystem::class);
    }

    /**
     * @test
     */
    public function export()
    {
        $organizationId = 'org-uuid';
        $organizationKnowledgebaseId = 'org-kb-uuid';
        $organizationName = 'Acme';
        $organizationSubdomain = 'acme';

        $organization = OrganizationModel::createFromArray([
            'id' => $organizationId,
            'knowledgebase_id' => $organizationKnowledgebaseId,
            'name' => $organizationName,
            'subdomain' => $organizationSubdomain,
        ]);

        $tempDir = '382iuwqj';
        $tempPathname = sys_get_temp_dir() . '/' . $tempDir;
        $organizationDirectoryName = 'Acme docs';
        $organizationDomain = sprintf('%s.%s', $organizationSubdomain, $this->environmentDomain);
        $team1Name = 'Engineering';
        $team2Name = 'Product';
        $teams = [
            [
                'knowledgebase_id' => 'team1-uuid',
                'name' => $team1Name,
            ],
            [
                'knowledgebase_id' => 'team2-uuid',
                'name' => $team2Name,
            ],
        ];
        $team1DirectoryName = 'Engineering team docs';
        $team2DirectoryName = 'Product team docs';
        $project1Name = 'Retention';
        $project2Name = 'Marketing website';
        $projects = [
            [
                'knowledgebase_id' => 'project1-uuid',
                'name' => $project1Name,
            ],
            [
                'knowledgebase_id' => 'project2-uuid',
                'name' => $project2Name,
            ],
        ];
        $project1DirectoryName = 'Retention project docs';
        $project2DirectoryName = 'Marketing website project docs';
        $zipDir = '9328eijw';
        $zipPathname = sys_get_temp_dir() . '/' . $zipDir;

        $this->createFilesystemFactoryExpectation();
        $this->createFileNameGeneratorGenerateRandomExpectation([], $tempDir);
        $this->createFilesystemMkdirExpectation([$tempPathname]);

        $this->createFileNameGeneratorGenerateFromStringExpectation(
            [$organizationDirectoryName],
            $organizationDirectoryName
        );
        $this->createFilesystemMkdirExpectation([$tempPathname . '/' . $organizationDirectoryName]);
        $this->createKnowledgebaseExporterExpectation([
            m::type(KnowledgebaseModel::class),
            $organizationDomain,
            $tempPathname . '/' . $organizationDirectoryName
        ]);

        $this->createTeamRepositoryExpectation([$organizationId], $teams);

        $this->createFileNameGeneratorGenerateFromStringExpectation([$team1DirectoryName], $team1DirectoryName);
        $this->createFilesystemMkdirExpectation([$tempPathname . '/' . $team1DirectoryName]);
        $this->createKnowledgebaseExporterExpectation([
            m::type(KnowledgebaseModel::class),
            $organizationDomain,
            $tempPathname . '/' . $team1DirectoryName
        ]);

        $this->createFileNameGeneratorGenerateFromStringExpectation([$team2DirectoryName], $team2DirectoryName);
        $this->createFilesystemMkdirExpectation([$tempPathname . '/' . $team2DirectoryName]);
        $this->createKnowledgebaseExporterExpectation([
            m::type(KnowledgebaseModel::class),
            $organizationDomain,
            $tempPathname . '/' . $team2DirectoryName
        ]);

        $this->createProjectRepositoryExpectation([$organizationId], $projects);

        $this->createFileNameGeneratorGenerateFromStringExpectation([$project1DirectoryName], $project1DirectoryName);
        $this->createFilesystemMkdirExpectation([$tempPathname . '/' . $project1DirectoryName]);
        $this->createKnowledgebaseExporterExpectation([
            m::type(KnowledgebaseModel::class),
            $organizationDomain,
            $tempPathname . '/' . $project1DirectoryName
        ]);

        $this->createFileNameGeneratorGenerateFromStringExpectation([$project2DirectoryName], $project2DirectoryName);
        $this->createFilesystemMkdirExpectation([$tempPathname . '/' . $project2DirectoryName]);
        $this->createKnowledgebaseExporterExpectation([
            m::type(KnowledgebaseModel::class),
            $organizationDomain,
            $tempPathname . '/' . $project2DirectoryName
        ]);

        $this->createFileNameGeneratorGenerateRandomExpectation(['zip'], $zipDir);
        $this->createZipArchiverExpectation([$zipPathname, $tempPathname]);
        $this->createFilesystemRemoveExpectation([$tempPathname]);

        $result = $this->organizationKnowledgeExporter->export($organization);
        $this->assertEquals($zipPathname, $result);
    }

    private function createFilesystemFactoryExpectation()
    {
        $this->filesystemFactory
            ->shouldReceive('create')
            ->once()
            ->andReturn($this->filesystem);
    }

    private function createFileNameGeneratorGenerateRandomExpectation($args, $result)
    {
        $this->fileNameGenerator
            ->shouldReceive('generateRandom')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createFilesystemMkdirExpectation($args)
    {
        $this->filesystem
            ->shouldReceive('mkdir')
            ->once()
            ->with(...$args);
    }

    private function createFileNameGeneratorGenerateFromStringExpectation($args, $result)
    {
        $this->fileNameGenerator
            ->shouldReceive('generateFromString')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createKnowledgebaseExporterExpectation($args)
    {
        $this->knowledgebaseExporter
            ->shouldReceive('export')
            ->once()
            ->with(...$args);
    }

    private function createProjectRepositoryExpectation($args, $result)
    {
        $this->projectRepository
            ->shouldReceive('getAll')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createTeamRepositoryExpectation($args, $result)
    {
        $this->teamRepository
            ->shouldReceive('getAll')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createFilesystemMakePathRelativeExpectation($args, $result)
    {
        $this->filesystem
            ->shouldReceive('makePathRelative')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createFilesystemRemoveExpectation($args)
    {
        $this->filesystem
            ->shouldReceive('remove')
            ->once()
            ->with(...$args);
    }

    private function createZipArchiverExpectation($args)
    {
        $this->zipArchiver
            ->shouldReceive('recursivelyArchiveDirectory')
            ->once()
            ->with(...$args);
    }
}
