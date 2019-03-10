<?php
declare(strict_types=1);

namespace LithosTests\Person\CreationStrategy;

use Doctrine\DBAL\Connection;
use Lithos\EmailAddressVerification\RequestEmailAddressVerification;
use Lithos\Organization\Organization;
use Lithos\Organization\OrganizationModel;
use Lithos\Person\CreationStrategy\CreateFoundingMember;
use Lithos\Person\PersonCreator;
use Lithos\Person\PersonModel;
use Lithos\Person\PersonCreationValidator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class CreateFoundingMemberTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $connection;
    private $organization;
    private $personCreationValidator;
    private $personCreator;
    private $requestEmailAddressVerification;
    private $createFoundingMember;

    public function setUp(): void
    {
        $this->connection = m::mock(Connection::class);
        $this->organization = m::mock(Organization::class);
        $this->personCreationValidator = m::mock(PersonCreationValidator::class);
        $this->personCreator = m::mock(PersonCreator::class);
        $this->requestEmailAddressVerification = m::mock(RequestEmailAddressVerification::class);

        $this->createFoundingMember = new CreateFoundingMember(
            $this->connection,
            $this->organization,
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

        $person = new PersonModel;
        $encodedPassword = 'encoded-password';

        $this->createPersonCreationValidatorExpectation($input);
        $this->createConnectionBeginTransactionExpectation();
        $this->createOrganizationExpectation($organization);
        $this->createPersonCreatorExpectation(
            $organization,
            $input['name'],
            $input['email_address'],
            $input['password'],
            [$person, $encodedPassword]
        );
        $this->createConnectionCommitExpectation();
        $this->createRequestEmailAddressVerificationExpectation($person);

        $result = $this->createFoundingMember->create($input);
        $this->assertEquals([$person, $encodedPassword], $result);
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
            ->andReturn($result);
    }

    private function createOrganizationExpectation($result)
    {
        $this->organization
            ->shouldReceive('create')
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
            ->with($input, 'founding_member');
    }
}
