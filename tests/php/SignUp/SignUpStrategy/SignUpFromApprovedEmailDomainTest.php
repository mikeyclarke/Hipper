<?php
declare(strict_types=1);

namespace Hipper\Tests\SignUp\SignUpStrategy;

use Doctrine\DBAL\Connection;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\Event\PersonCreatedEvent;
use Hipper\Person\PersonCreator;
use Hipper\Person\PersonModel;
use Hipper\Person\PersonRepository;
use Hipper\SignUp\Exception\AuthorizationRequestForeignToOrganizationException;
use Hipper\SignUp\Exception\AuthorizationRequestMissingOrganizationIdException;
use Hipper\SignUp\Exception\EmailAddressAlreadyInUseException;
use Hipper\SignUp\SignUpAuthorization;
use Hipper\SignUp\SignUpAuthorizationRequestModel;
use Hipper\SignUp\SignUpStrategy\SignUpFromApprovedEmailDomain;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SignUpFromApprovedEmailDomainTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $connection;
    private $eventDispatcher;
    private $personCreator;
    private $personRepository;
    private $signUpAuthorization;
    private $signUpFromApprovedEmailDomain;

    public function setUp(): void
    {
        $this->connection = m::mock(Connection::class);
        $this->eventDispatcher = m::mock(EventDispatcherInterface::class);
        $this->personCreator = m::mock(PersonCreator::class);
        $this->personRepository = m::mock(PersonRepository::class);
        $this->signUpAuthorization = m::mock(SignUpAuthorization::class);

        $this->signUpFromApprovedEmailDomain = new SignUpFromApprovedEmailDomain(
            $this->connection,
            $this->eventDispatcher,
            $this->personCreator,
            $this->personRepository,
            $this->signUpAuthorization
        );
    }

    /**
     * @test
     */
    public function signUp()
    {
        $emailAddress = 'mikey@usehipper.com';
        $name = 'Mikey Clarke';
        $encodedPassword = 'encoded-password';

        $authorizationRequest = SignUpAuthorizationRequestModel::createFromArray([
            'organization_id' => 'org-uuid',
            'email_address' => $emailAddress,
            'encoded_password' => $encodedPassword,
            'name' => $name,
        ]);
        $organization = OrganizationModel::createFromArray([
            'id' => 'org-uuid',
        ]);
        $input = ['phrase' => 'foo bar baz qux'];

        $person = new PersonModel;

        $this->createSignUpAuthorizationExpectation([$authorizationRequest, $input]);
        $this->createPersonRepositoryExpectation([$emailAddress], false);
        $this->createConnectionBeginTransactionExpectation();
        $this->createPersonCreatorExpectation([$organization, $name, $emailAddress, $encodedPassword], $person);
        $this->createConnectionCommitExpectation();
        $this->createEventDispatcherExpectation(
            [m::type(PersonCreatedEvent::class), PersonCreatedEvent::NAME]
        );

        $result = $this->signUpFromApprovedEmailDomain->signUp($authorizationRequest, $organization, $input);
        $this->assertEquals($person, $result);
    }

    /**
     * @test
     */
    public function authorizationRequestMissingOrganizationId()
    {
        $authorizationRequest = SignUpAuthorizationRequestModel::createFromArray([
            'organization_id' => null,
        ]);
        $organization = OrganizationModel::createFromArray([
            'id' => 'org-uuid',
        ]);
        $input = ['phrase' => 'foo bar baz qux'];

        $this->createSignUpAuthorizationExpectation([$authorizationRequest, $input]);

        $this->expectException(AuthorizationRequestMissingOrganizationIdException::class);

        $this->signUpFromApprovedEmailDomain->signUp($authorizationRequest, $organization, $input);
    }

    /**
     * @test
     */
    public function authorizationRequestForeignToOrganization()
    {
        $authorizationRequest = SignUpAuthorizationRequestModel::createFromArray([
            'organization_id' => 'foreign-org-uuid',
        ]);
        $organization = OrganizationModel::createFromArray([
            'id' => 'org-uuid',
        ]);
        $input = ['phrase' => 'foo bar baz qux'];

        $this->createSignUpAuthorizationExpectation([$authorizationRequest, $input]);

        $this->expectException(AuthorizationRequestForeignToOrganizationException::class);

        $this->signUpFromApprovedEmailDomain->signUp($authorizationRequest, $organization, $input);
    }

    /**
     * @test
     */
    public function emailAddressAlreadyInUse()
    {
        $emailAddress = 'mikey@usehipper.com';

        $authorizationRequest = SignUpAuthorizationRequestModel::createFromArray([
            'organization_id' => 'org-uuid',
            'email_address' => $emailAddress,
        ]);
        $organization = OrganizationModel::createFromArray([
            'id' => 'org-uuid',
        ]);
        $input = ['phrase' => 'foo bar baz qux'];

        $this->createSignUpAuthorizationExpectation([$authorizationRequest, $input]);
        $this->createPersonRepositoryExpectation([$emailAddress], true);

        $this->expectException(EmailAddressAlreadyInUseException::class);

        $this->signUpFromApprovedEmailDomain->signUp($authorizationRequest, $organization, $input);
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

    private function createPersonRepositoryExpectation($args, $result)
    {
        $this->personRepository
            ->shouldReceive('existsWithEmailAddress')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createSignUpAuthorizationExpectation($args)
    {
        $this->signUpAuthorization
            ->shouldReceive('authorize')
            ->once()
            ->with(...$args);
    }
}
