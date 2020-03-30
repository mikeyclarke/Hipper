<?php
declare(strict_types=1);

namespace Hipper\Tests\SignUp\AuthorizationStrategy;

use Hipper\Organization\OrganizationModel;
use Hipper\Person\PersonPasswordEncoder;
use Hipper\SignUp\AuthorizationStrategy\ApprovedEmailDomainSignUpAuthorization;
use Hipper\SignUp\AuthorizationValidation\ApprovedEmailDomainSignUpAuthorizationValidator;
use Hipper\SignUp\SignUpAuthorization;
use Hipper\SignUp\SignUpAuthorizationRequestModel;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ApprovedEmailDomainSignUpAuthorizationTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $validator;
    private $passwordEncoder;
    private $signUpAuthorization;
    private $approvedEmailDomainSignUpAuthorization;

    public function setUp(): void
    {
        $this->validator = m::mock(ApprovedEmailDomainSignUpAuthorizationValidator::class);
        $this->passwordEncoder = m::mock(PersonPasswordEncoder::class);
        $this->signUpAuthorization = m::mock(SignUpAuthorization::class);

        $this->approvedEmailDomainSignUpAuthorization = new ApprovedEmailDomainSignUpAuthorization(
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
        $organizationId = 'org-uuid';
        $emailAddress = 'mikey@usehipper.com';
        $name = 'Mikey Clarke';
        $password = 'p455w0rd';
        $termsAgreed = true;

        $organization = OrganizationModel::createFromArray([
            'id' => $organizationId,
        ]);
        $input = [
            'email_address' => $emailAddress,
            'name' => $name,
            'password' => $password,
            'terms_agreed' => $termsAgreed,
        ];

        $encodedPassword = 'encoded-password';
        $authorizationRequest = new SignUpAuthorizationRequestModel;

        $this->createValidatorExpectation([$input, $organization]);
        $this->createPasswordEncoderExpectation([$password], $encodedPassword);
        $this->createSignUpAuthorizationExpectation(
            [$emailAddress, $name, $encodedPassword, $organizationId],
            $authorizationRequest
        );

        $result = $this->approvedEmailDomainSignUpAuthorization->request($organization, $input);
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
