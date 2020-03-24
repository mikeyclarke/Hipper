<?php
declare(strict_types=1);

namespace Hipper\Tests\SignUpAuthentication;

use Hipper\EmailAddressVerification\VerificationPhraseGenerator;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\PersonCreationValidator;
use Hipper\Person\PersonPasswordEncoder;
use Hipper\SignUpAuthentication\SignUpAuthenticationModel;
use Hipper\SignUpAuthentication\SignUpAuthenticationRequest;
use Hipper\SignUpAuthentication\Storage\SignUpAuthenticationInserter;
use Hipper\TransactionalEmail\VerifyEmailAddressEmail;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class SignUpAuthenticationRequestTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $idGenerator;
    private $personCreationValidator;
    private $passwordEncoder;
    private $inserter;
    private $verificationPhraseGenerator;
    private $verifyEmailAddressEmail;
    private $signUpAuthenticationRequest;

    public function setUp(): void
    {
        $this->idGenerator = m::mock(IdGenerator::class);
        $this->personCreationValidator = m::mock(PersonCreationValidator::class);
        $this->passwordEncoder = m::mock(PersonPasswordEncoder::class);
        $this->inserter = m::mock(SignUpAuthenticationInserter::class);
        $this->verificationPhraseGenerator = m::mock(VerificationPhraseGenerator::class);
        $this->verifyEmailAddressEmail = m::mock(VerifyEmailAddressEmail::class);

        $this->signUpAuthenticationRequest = new SignUpAuthenticationRequest(
            $this->idGenerator,
            $this->personCreationValidator,
            $this->passwordEncoder,
            $this->inserter,
            $this->verificationPhraseGenerator,
            $this->verifyEmailAddressEmail
        );
    }

    /**
     * @test
     */
    public function create()
    {
        $name = 'James Holden';
        $emailAddress = 'jh@example.com';
        $password = 'p455w0rd';
        $input = [
            'name' => $name,
            'email_address' => $emailAddress,
            'password' => $password,
        ];

        $authenticationRequestId = 'auth-req-uuid';
        $verificationPhrase = 'alter berlin paint meaning';
        $encodedPassword = 'encoded-password';

        $this->createPersonCreationValidatorExpectation([$input, null, ['sign_up_authentication']]);
        $this->createIdGeneratorExpectation($authenticationRequestId);
        $this->createVerificationPhraseGeneratorExpectation($verificationPhrase);
        $this->createPasswordEncoderExpectation([$password], $encodedPassword);
        $this->createSignUpAuthenticationInserterExpectation(
            [$authenticationRequestId, $verificationPhrase, $emailAddress, $name, $encodedPassword, null]
        );
        $this->createVerifyEmailAddressEmailExpectation([$name, $emailAddress, $verificationPhrase]);

        $result = $this->signUpAuthenticationRequest->create($input);
        $this->assertInstanceOf(SignUpAuthenticationModel::class, $result);
        $this->assertEquals($authenticationRequestId, $result->getId());
        $this->assertEquals($name, $result->getName());
        $this->assertEquals($emailAddress, $result->getEmailAddress());
        $this->assertEquals($encodedPassword, $result->getEncodedPassword());
        $this->assertEquals($verificationPhrase, $result->getVerificationPhrase());
        $this->assertEquals(null, $result->getOrganizationId());
    }

    /**
     * @test
     */
    public function createForApprovedEmailDomain()
    {
        $name = 'James Holden';
        $emailAddress = 'jh@example.com';
        $password = 'p455w0rd';
        $input = [
            'name' => $name,
            'email_address' => $emailAddress,
            'password' => $password,
        ];
        $organizationId = 'org-uuid';
        $organization = OrganizationModel::createFromArray([
            'id' => $organizationId,
        ]);
        $validationGroups = ['approved_email_domain'];

        $authenticationRequestId = 'auth-req-uuid';
        $verificationPhrase = 'alter berlin paint meaning';
        $encodedPassword = 'encoded-password';

        $this->createPersonCreationValidatorExpectation(
            [$input, $organization, ['sign_up_authentication', 'approved_email_domain']]
        );
        $this->createIdGeneratorExpectation($authenticationRequestId);
        $this->createVerificationPhraseGeneratorExpectation($verificationPhrase);
        $this->createPasswordEncoderExpectation([$password], $encodedPassword);
        $this->createSignUpAuthenticationInserterExpectation(
            [$authenticationRequestId, $verificationPhrase, $emailAddress, $name, $encodedPassword, $organizationId]
        );
        $this->createVerifyEmailAddressEmailExpectation([$name, $emailAddress, $verificationPhrase]);

        $result = $this->signUpAuthenticationRequest->create($input, $organization, $validationGroups);
        $this->assertInstanceOf(SignUpAuthenticationModel::class, $result);
        $this->assertEquals($authenticationRequestId, $result->getId());
        $this->assertEquals($name, $result->getName());
        $this->assertEquals($emailAddress, $result->getEmailAddress());
        $this->assertEquals($encodedPassword, $result->getEncodedPassword());
        $this->assertEquals($verificationPhrase, $result->getVerificationPhrase());
        $this->assertEquals($organizationId, $result->getOrganizationId());
    }

    private function createVerifyEmailAddressEmailExpectation($args)
    {
        $this->verifyEmailAddressEmail
            ->shouldReceive('send')
            ->once()
            ->with(...$args);
    }

    private function createSignUpAuthenticationInserterExpectation($args)
    {
        $this->inserter
            ->shouldReceive('insert')
            ->once()
            ->with(...$args);
    }

    private function createPasswordEncoderExpectation($args, $result)
    {
        $this->passwordEncoder
            ->shouldReceive('encodePassword')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createVerificationPhraseGeneratorExpectation($result)
    {
        $this->verificationPhraseGenerator
            ->shouldReceive('generate')
            ->once()
            ->andReturn($result);
    }

    private function createIdGeneratorExpectation($result)
    {
        $this->idGenerator
            ->shouldReceive('generate')
            ->once()
            ->andReturn($result);
    }

    private function createPersonCreationValidatorExpectation($args)
    {
        $this->personCreationValidator
            ->shouldReceive('validate')
            ->once()
            ->with(...$args);
    }
}
