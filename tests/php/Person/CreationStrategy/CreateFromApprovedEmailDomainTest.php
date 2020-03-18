<?php
declare(strict_types=1);

namespace Hipper\Tests\Person\CreationStrategy;

use Doctrine\DBAL\Connection;
use Hipper\EmailAddressVerification\RequestEmailAddressVerification;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\CreationStrategy\CreateFromApprovedEmailDomain;
use Hipper\Person\Event\PersonCreatedEvent;
use Hipper\Person\Exception\ApprovedEmailDomainSignupNotAllowedException;
use Hipper\Person\PersonCreationValidator;
use Hipper\Person\PersonCreator;
use Hipper\Person\PersonModel;
use Hipper\Validation\Exception\ValidationException;
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
    private $requestEmailAddressVerification;
    private $createFromApprovedEmailDomain;

    public function setUp(): void
    {
        $this->connection = m::mock(Connection::class);
        $this->eventDispatcher = m::mock(EventDispatcherInterface::class);
        $this->personCreationValidator = m::mock(PersonCreationValidator::class);
        $this->personCreator = m::mock(PersonCreator::class);
        $this->requestEmailAddressVerification = m::mock(RequestEmailAddressVerification::class);

        $this->createFromApprovedEmailDomain = new CreateFromApprovedEmailDomain(
            $this->connection,
            $this->eventDispatcher,
            $this->personCreationValidator,
            $this->personCreator,
            $this->requestEmailAddressVerification
        );
    }

    /**
     * @test
     */
    public function create()
    {
        $this->markTestSkipped();

        $input = [
            'name' => 'Mikey Clarke',
            'email_address' => 'mikey@usehipper.com',
            'password' => '32gyewg7sy',
        ];
        $organization = new OrganizationModel;
        $organization->setApprovedEmailDomainSignupAllowed(true);
        $organization->setApprovedEmailDomains('["usehipper.com"]');

        $person = new PersonModel;
        $encodedPassword = 'encoded-password';

        $this->createPersonCreationValidatorExpectation($input);
        $this->createConnectionBeginTransactionExpectation();
        $this->createPersonCreatorExpectation(
            $organization,
            $input['name'],
            $input['email_address'],
            $input['password'],
            [$person, $encodedPassword]
        );
        $this->createConnectionCommitExpectation();
        $this->createRequestEmailAddressVerificationExpectation($person);
        $this->createEventDispatcherExpectation([m::type(PersonCreatedEvent::class), PersonCreatedEvent::NAME]);

        $result = $this->createFromApprovedEmailDomain->create($organization, $input);
        $this->assertEquals([$person, $encodedPassword], $result);
    }

    /**
     * @test
     */
    public function cannotSignupIfEmailDomainIsNotApproved()
    {
        $this->markTestSkipped();

        $this->expectException(ValidationException::class);

        $input = [
            'email_address' => 'mikey@lithosapp.com',
        ];
        $organization = new OrganizationModel;
        $organization->setApprovedEmailDomainSignupAllowed(true);
        $organization->setApprovedEmailDomains('["usehipper.test", "usehipper.com"]');

        $this->createPersonCreationValidatorExpectation($input);

        $this->createFromApprovedEmailDomain->create($organization, $input);
    }

    /**
     * @test
     */
    public function cannotSignupIfApprovedEmailDomainSignupDisabled()
    {
        $this->markTestSkipped();

        $this->expectException(ApprovedEmailDomainSignupNotAllowedException::class);

        $input = [];
        $organization = new OrganizationModel;
        $organization->setApprovedEmailDomainSignupAllowed(false);

        $this->createPersonCreationValidatorExpectation($input);

        $this->createFromApprovedEmailDomain->create($organization, $input);
    }

    private function createEventDispatcherExpectation($args)
    {
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->once()
            ->with(...$args);
    }

    private function createRequestEmailAddressVerificationExpectation($person)
    {
        $this->requestEmailAddressVerification
            ->shouldReceive('sendVerificationRequest')
            ->once()
            ->with($person);
    }

    private function createConnectionCommitExpectation()
    {
        $this->connection
            ->shouldReceive('commit')
            ->once();
    }

    private function createPersonCreatorExpectation($organization, $name, $emailAddress, $password, $result)
    {
        $this->personCreator
            ->shouldReceive('create')
            ->once()
            ->with($organization, $name, $emailAddress, $password)
            ->once()
            ->andReturn($result);
    }

    private function createConnectionBeginTransactionExpectation()
    {
        $this->connection
            ->shouldReceive('beginTransaction')
            ->once();
    }

    private function createPersonCreationValidatorExpectation($input)
    {
        $this->personCreationValidator
            ->shouldReceive('validate')
            ->once()
            ->with($input, 'approved_email_domain');
    }
}
