<?php
declare(strict_types=1);

namespace Hipper\Tests\SignUp;

use Hipper\EmailAddressVerification\VerificationPhraseGenerator;
use Hipper\IdGenerator\IdGenerator;
use Hipper\SignUp\SignUpAuthorization;
use Hipper\SignUp\SignUpAuthorizationRequestModel;
use Hipper\SignUp\Storage\SignUpAuthorizationRequestInserter;
use Hipper\TransactionalEmail\VerifyEmailAddressEmail;
use Hipper\Validation\Exception\ValidationException;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SignUpAuthorizationTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $idGenerator;
    private $inserter;
    private $validatorInterface;
    private $verificationPhraseGenerator;
    private $verifyEmailAddressEmail;
    private $signUpAuthorization;

    public function setUp(): void
    {
        $this->idGenerator = m::mock(IdGenerator::class);
        $this->inserter = m::mock(SignUpAuthorizationRequestInserter::class);
        $this->validatorInterface = m::mock(ValidatorInterface::class);
        $this->verificationPhraseGenerator = m::mock(VerificationPhraseGenerator::class);
        $this->verifyEmailAddressEmail = m::mock(VerifyEmailAddressEmail::class);

        $this->signUpAuthorization = new SignUpAuthorization(
            $this->idGenerator,
            $this->inserter,
            $this->validatorInterface,
            $this->verificationPhraseGenerator,
            $this->verifyEmailAddressEmail
        );
    }

    /**
     * @test
     */
    public function requestWithOrganizationName()
    {
        $emailAddress = 'jh@example.com';
        $name = 'James Holden';
        $encodedPassword = 'encoded-password';
        $organizationId = null;
        $organizationName = 'Acme';

        $authorizationRequestId = 'auth-req-uuid';
        $verificationPhrase = 'alter berlin paint meaning';

        $this->createIdGeneratorExpectation($authorizationRequestId);
        $this->createVerificationPhraseGeneratorExpectation($verificationPhrase);
        $this->createSignUpAuthorizationRequestInserterExpectation([
            $authorizationRequestId,
            $verificationPhrase,
            $emailAddress,
            $name,
            $encodedPassword,
            $organizationId,
            $organizationName
        ]);
        $this->createVerifyEmailAddressEmailExpectation([$name, $emailAddress, $verificationPhrase]);

        $result = $this->signUpAuthorization->request(
            $emailAddress,
            $name,
            $encodedPassword,
            $organizationId,
            $organizationName
        );
        $this->assertInstanceOf(SignUpAuthorizationRequestModel::class, $result);
        $this->assertEquals($authorizationRequestId, $result->getId());
        $this->assertEquals($name, $result->getName());
        $this->assertEquals($emailAddress, $result->getEmailAddress());
        $this->assertEquals($encodedPassword, $result->getEncodedPassword());
        $this->assertEquals($verificationPhrase, $result->getVerificationPhrase());
        $this->assertEquals($organizationId, $result->getOrganizationId());
        $this->assertEquals($organizationName, $result->getOrganizationName());
    }

    /**
     * @test
     */
    public function requestWithOrganizationId()
    {
        $emailAddress = 'jh@example.com';
        $name = 'James Holden';
        $encodedPassword = 'encoded-password';
        $organizationId = 'org-uuid';
        $organizationName = null;

        $authorizationRequestId = 'auth-req-uuid';
        $verificationPhrase = 'alter berlin paint meaning';

        $this->createIdGeneratorExpectation($authorizationRequestId);
        $this->createVerificationPhraseGeneratorExpectation($verificationPhrase);
        $this->createSignUpAuthorizationRequestInserterExpectation([
            $authorizationRequestId,
            $verificationPhrase,
            $emailAddress,
            $name,
            $encodedPassword,
            $organizationId,
            $organizationName
        ]);
        $this->createVerifyEmailAddressEmailExpectation([$name, $emailAddress, $verificationPhrase]);

        $result = $this->signUpAuthorization->request(
            $emailAddress,
            $name,
            $encodedPassword,
            $organizationId,
            $organizationName
        );
        $this->assertInstanceOf(SignUpAuthorizationRequestModel::class, $result);
        $this->assertEquals($authorizationRequestId, $result->getId());
        $this->assertEquals($name, $result->getName());
        $this->assertEquals($emailAddress, $result->getEmailAddress());
        $this->assertEquals($encodedPassword, $result->getEncodedPassword());
        $this->assertEquals($verificationPhrase, $result->getVerificationPhrase());
        $this->assertEquals($organizationId, $result->getOrganizationId());
        $this->assertEquals($organizationName, $result->getOrganizationName());
    }

    /**
     * @test
     */
    public function authorize()
    {
        $authorizationRequest = new SignUpAuthorizationRequestModel;
        $input = [
            'phrase' => 'it’s the correct phrase',
        ];

        $this->createValidatorInterfaceExpectation([$input, m::type(Collection::class)], new ConstraintViolationList);

        $this->signUpAuthorization->authorize($authorizationRequest, $input);
    }

    /**
     * @test
     */
    public function authorizeThrowsExceptionOnFail()
    {
        $authorizationRequest = new SignUpAuthorizationRequestModel;
        $input = [
            'phrase' => 'not the correct phrase',
        ];

        $this->createValidatorInterfaceExpectation(
            [$input, m::type(Collection::class)],
            new ConstraintViolationList([
                new ConstraintViolation(
                    'message',
                    'message',
                    [],
                    $input,
                    'phrase',
                    $input['phrase']
                )
            ])
        );

        $this->expectException(ValidationException::class);

        $this->signUpAuthorization->authorize($authorizationRequest, $input);
    }

    private function createValidatorInterfaceExpectation($args, $result)
    {
        $this->validatorInterface
            ->shouldReceive('validate')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createVerifyEmailAddressEmailExpectation($args)
    {
        $this->verifyEmailAddressEmail
            ->shouldReceive('send')
            ->once()
            ->with(...$args);
    }

    private function createSignUpAuthorizationRequestInserterExpectation($args)
    {
        $this->inserter
            ->shouldReceive('insert')
            ->once()
            ->with(...$args);
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
}
