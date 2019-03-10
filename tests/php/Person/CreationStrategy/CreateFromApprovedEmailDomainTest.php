<?php
declare(strict_types=1);

namespace Lithos\Tests\Person\CreationStrategy;

use Doctrine\DBAL\Connection;
use Lithos\EmailAddressVerification\RequestEmailAddressVerification;
use Lithos\Organization\OrganizationModel;
use Lithos\Person\CreationStrategy\CreateFromApprovedEmailDomain;
use Lithos\Person\Exception\ApprovedEmailDomainSignupNotAllowedException;
use Lithos\Person\PersonCreationValidator;
use Lithos\Person\PersonCreator;
use Lithos\Person\PersonModel;
use Lithos\Validation\Exception\ValidationException;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class CreateFromApprovedEmailDomainTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $connection;
    private $personCreationValidator;
    private $personCreator;
    private $requestEmailAddressVerification;
    private $createFromApprovedEmailDomain;

    public function setUp(): void
    {
        $this->connection = m::mock(Connection::class);
        $this->personCreationValidator = m::mock(PersonCreationValidator::class);
        $this->personCreator = m::mock(PersonCreator::class);
        $this->requestEmailAddressVerification = m::mock(RequestEmailAddressVerification::class);

        $this->createFromApprovedEmailDomain = new CreateFromApprovedEmailDomain(
            $this->connection,
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
        $input = [
            'name' => 'Mikey Clarke',
            'email_address' => 'mikey@tryhleo.com',
            'password' => '32gyewg7sy',
        ];
        $organization = new OrganizationModel;
        $organization->setApprovedEmailDomainSignupAllowed(true);
        $organization->setApprovedEmailDomains('["tryhleo.com"]');

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

        $result = $this->createFromApprovedEmailDomain->create($organization, $input);
        $this->assertEquals([$person, $encodedPassword], $result);
    }

    /**
     * @test
     */
    public function cannotSignupIfEmailDomainIsNotApproved()
    {
        $this->expectException(ValidationException::class);

        $input = [
            'email_address' => 'mikey@lithosapp.com',
        ];
        $organization = new OrganizationModel;
        $organization->setApprovedEmailDomainSignupAllowed(true);
        $organization->setApprovedEmailDomains('["tryhleo.test", "tryhleo.com"]');

        $this->createPersonCreationValidatorExpectation($input);

        $this->createFromApprovedEmailDomain->create($organization, $input);
    }

    /**
     * @test
     */
    public function cannotSignupIfApprovedEmailDomainSignupDisabled()
    {
        $this->expectException(ApprovedEmailDomainSignupNotAllowedException::class);

        $input = [];
        $organization = new OrganizationModel;
        $organization->setApprovedEmailDomainSignupAllowed(false);

        $this->createPersonCreationValidatorExpectation($input);

        $this->createFromApprovedEmailDomain->create($organization, $input);
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
