<?php

declare(strict_types=1);

namespace Hipper\Tests\Organization;

use Hipper\Messenger\MessageBus;
use Hipper\Messenger\Message\OrganizationKnowledgeExportRequest;
use Hipper\Organization\OrganizationModel;
use Hipper\Organization\RequestKnowledgeExport;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestKnowledgeExportTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $messageBus;
    private $validator;
    private $requestKnowledgeExport;

    public function setUp(): void
    {
        $this->messageBus = m::mock(MessageBus::class);
        $this->validator = m::mock(ValidatorInterface::class);

        $this->requestKnowledgeExport = new RequestKnowledgeExport(
            $this->messageBus,
            $this->validator
        );
    }

    /**
     * @test
     */
    public function createRequest()
    {
        $organization = OrganizationModel::createFromArray([
            'id' => 'org-uuid',
        ]);
        $input = [
            'recipient_email_addresses' => ['mikey@usehipper.com', 'team@usehipper.com'],
        ];

        $this->createValidatorExpectation([$input, m::type(Collection::class)], new ConstraintViolationList());
        $this->createMessageBusExpectation([m::type(OrganizationKnowledgeExportRequest::class)]);

        $this->requestKnowledgeExport->createRequest($organization, $input);
    }

    private function createValidatorExpectation($args, $result)
    {
        $this->validator
            ->shouldReceive('validate')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createMessageBusExpectation($args)
    {
        $this->messageBus
            ->shouldReceive('dispatch')
            ->once()
            ->with(...$args);
    }
}
