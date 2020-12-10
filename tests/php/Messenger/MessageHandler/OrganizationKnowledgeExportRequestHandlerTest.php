<?php

declare(strict_types=1);

namespace Hipper\Tests\Messenger\MessageHandler;

use Hipper\Messenger\MessageHandler\OrganizationKnowledgeExportRequestHandler;
use Hipper\Messenger\Message\OrganizationKnowledgeExportRequest;
use Hipper\Organization\OrganizationKnowledgeExporter;
use Hipper\Organization\OrganizationModel;
use Hipper\Organization\OrganizationRepository;
use Hipper\TransactionalEmail\OrganizationKnowledgeExport as OrganizationKnowledgeExportEmail;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;

class OrganizationKnowledgeExportRequestHandlerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $organizationKnowledgeExportEmail;
    private $organizationKnowledgeExporter;
    private $organizationRepository;
    private $handler;

    public function setUp(): void
    {
        $this->organizationKnowledgeExportEmail = m::mock(OrganizationKnowledgeExportEmail::class);
        $this->organizationKnowledgeExporter = m::mock(OrganizationKnowledgeExporter::class);
        $this->organizationRepository = m::mock(OrganizationRepository::class);

        $this->handler = new OrganizationKnowledgeExportRequestHandler(
            $this->organizationKnowledgeExportEmail,
            $this->organizationKnowledgeExporter,
            $this->organizationRepository
        );

        vfsStream::setup('foo');
    }

    /**
     * @test
     */
    public function __invoke()
    {
        $organizationId = 'org-uuid';
        $recipientEmailAddresses = [
            'mikey@usehipper.com',
        ];
        $message = new OrganizationKnowledgeExportRequest($organizationId, $recipientEmailAddresses);

        $pathname = 'vfs://foo/bar.zip';
        $zipContents = 'zip contents';
        file_put_contents($pathname, $zipContents);

        $organizationResult = ['id' => $organizationId];

        $this->createOrganizationRepositoryExpectation([$organizationId], $organizationResult);
        $this->createOrganizationKnowledgeExporterExpectation([m::type(OrganizationModel::class)], $pathname);
        $this->createOrganizationKnowledgeExportEmail([$recipientEmailAddresses, base64_encode($zipContents)]);

        $this->handler->__invoke($message);

        $this->assertFalse(file_exists($pathname));
    }

    private function createOrganizationRepositoryExpectation($args, $result)
    {
        $this->organizationRepository
            ->shouldReceive('findById')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createOrganizationKnowledgeExporterExpectation($args, $result)
    {
        $this->organizationKnowledgeExporter
            ->shouldReceive('export')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createOrganizationKnowledgeExportEmail($args)
    {
        $this->organizationKnowledgeExportEmail
            ->shouldReceive('send')
            ->once()
            ->with(...$args);
    }
}
