<?php
declare(strict_types=1);

namespace Lithos\Person\CreationStrategy;

use Lithos\EmailAddressVerification\RequestEmailAddressVerification;
use Lithos\Organization\Organization;
use Lithos\Organization\OrganizationModel;
use Lithos\Person\CreationStrategy\CreateFoundingMember;
use Lithos\Person\PersonCreator;
use Lithos\Person\PersonModel;
use Lithos\Person\PersonValidator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class CreateFoundingMemberTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $organization;
    private $personCreator;
    private $personValidator;
    private $requestEmailAddressVerification;
    private $createFoundingMember;

    public function setUp(): void
    {
        $this->organization = m::mock(Organization::class);
        $this->personCreator = m::mock(PersonCreator::class);
        $this->personValidator = m::mock(PersonValidator::class);
        $this->requestEmailAddressVerification = m::mock(RequestEmailAddressVerification::class);

        $this->createFoundingMember = new CreateFoundingMember(
            $this->organization,
            $this->personCreator,
            $this->personValidator,
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

        $this->createPersonValidatorExpectation($input);
        $this->createOrganizationExpectation($organization);
        $this->createPersonCreatorExpectation(
            $organization,
            $input['name'],
            $input['email_address'],
            $input['password'],
            [$person, $encodedPassword]
        );
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

    private function createPersonValidatorExpectation($input)
    {
        $this->personValidator
            ->shouldReceive('validate')
            ->once()
            ->with($input, true);
    }
}
