<?php
declare(strict_types=1);

namespace Hipper\Tests\Person\CreationStrategy;

use Doctrine\DBAL\Connection;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\CreationStrategy\CreateFromApprovedEmailDomain;
use Hipper\Person\Event\PersonCreatedEvent;
use Hipper\Person\PersonCreationValidator;
use Hipper\Person\PersonCreator;
use Hipper\Person\PersonModel;
use Hipper\SignUpAuthentication\SignUpAuthenticationModel;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CreateFromApprovedEmailDomainTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $connection;
    private $eventDispatcher;
    private $personCreationValidator;
    private $personCreator;
    private $createFromApprovedEmailDomain;

    public function setUp(): void
    {
        $this->connection = m::mock(Connection::class);
        $this->eventDispatcher = m::mock(EventDispatcherInterface::class);
        $this->personCreationValidator = m::mock(PersonCreationValidator::class);
        $this->personCreator = m::mock(PersonCreator::class);

        $this->createFromApprovedEmailDomain = new CreateFromApprovedEmailDomain(
            $this->connection,
            $this->eventDispatcher,
            $this->personCreationValidator,
            $this->personCreator
        );
    }

    /**
     * @test
     */
    public function create()
    {
        $organization = new OrganizationModel;
        $name = 'Mikey Clarke';
        $emailAddress = 'mikey@usehipper.com';
        $encodedPassword = 'encoded-password';
        $signUpAuthentication = SignUpAuthenticationModel::createFromArray([
            'name' => $name,
            'email_address' => $emailAddress,
            'encoded_password' => $encodedPassword,
        ]);

        $validationParameters = [
            'email_address' => $emailAddress,
            'name' => $name,
        ];

        $person = new PersonModel;

        $this->createPersonCreationValidatorExpectation(
            [$validationParameters, $organization, ['approved_email_domain']]
        );
        $this->createConnectionBeginTransactionExpectation();
        $this->createPersonCreatorExpectation([$organization, $name, $emailAddress, $encodedPassword], $person);
        $this->createConnectionCommitExpectation();
        $this->createEventDispatcherExpectation([m::type(PersonCreatedEvent::class), PersonCreatedEvent::NAME]);

        $result = $this->createFromApprovedEmailDomain->create($organization, $signUpAuthentication);
        $this->assertEquals($person, $result);
    }

    private function createEventDispatcherExpectation($args)
    {
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->once()
            ->with(...$args);
    }

    private function createConnectionCommitExpectation()
    {
        $this->connection
            ->shouldReceive('commit')
            ->once();
    }

    private function createPersonCreatorExpectation($args, $result)
    {
        $this->personCreator
            ->shouldReceive('createWithEncodedPassword')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createConnectionBeginTransactionExpectation()
    {
        $this->connection
            ->shouldReceive('beginTransaction')
            ->once();
    }

    private function createPersonCreationValidatorExpectation($args)
    {
        $this->personCreationValidator
            ->shouldReceive('validate')
            ->once()
            ->with(...$args);
    }
}
