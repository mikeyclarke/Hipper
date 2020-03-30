<?php
declare(strict_types=1);

namespace Hipper\Tests\SignUp\AuthorizationStrategy;

use Hipper\Person\PersonPasswordEncoder;
use Hipper\SignUp\AuthorizationStrategy\FoundingMemberSignUpAuthorization;
use Hipper\SignUp\AuthorizationValidation\FoundingMemberSignUpAuthorizationValidator;
use Hipper\SignUp\SignUpAuthorization;
use Hipper\SignUp\SignUpAuthorizationRequestModel;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class FoundingMemberSignUpAuthorizationTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $validator;
    private $passwordEncoder;
    private $signUpAuthorization;
    private $foundingMemberSignUpAuthorization;

    public function setUp(): void
    {
        $this->validator = m::mock(FoundingMemberSignUpAuthorizationValidator::class);
        $this->passwordEncoder = m::mock(PersonPasswordEncoder::class);
        $this->signUpAuthorization = m::mock(SignUpAuthorization::class);

        $this->foundingMemberSignUpAuthorization = new FoundingMemberSignUpAuthorization(
            $this->validator,
            $this->passwordEncoder,
            $this->signUpAuthorization
        );
    }

    /**
     * @test
     */
    public function request()
    {
        $organizationName = 'Acme';
        $emailAddress = 'mikey@usehipper.com';
        $name = 'Mikey Clarke';
        $password = 'p455w0rd';
        $termsAgreed = true;

        $input = [
            'organization_name' => $organizationName,
            'email_address' => $emailAddress,
            'name' => $name,
            'password' => $password,
            'terms_agreed' => $termsAgreed,
        ];

        $encodedPassword = 'encoded-password';
        $authorizationRequest = new SignUpAuthorizationRequestModel;

        $this->createValidatorExpectation([$input]);
        $this->createPasswordEncoderExpectation([$password], $encodedPassword);
        $this->createSignUpAuthorizationExpectation(
            [$emailAddress, $name, $encodedPassword, null, $organizationName],
            $authorizationRequest
        );

        $result = $this->foundingMemberSignUpAuthorization->request($input);
        $this->assertInstanceOf(SignUpAuthorizationRequestModel::class, $result);
    }

    private function createSignUpAuthorizationExpectation($args, $result)
    {
        $this->signUpAuthorization
            ->shouldReceive('request')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createPasswordEncoderExpectation($args, $result)
    {
        $this->passwordEncoder
            ->shouldReceive('encodePassword')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createValidatorExpectation($args)
    {
        $this->validator
            ->shouldReceive('validate')
            ->once()
            ->with(...$args);
    }
}
