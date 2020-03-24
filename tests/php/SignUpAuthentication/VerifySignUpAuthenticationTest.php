<?php
declare(strict_types=1);

namespace Hipper\Tests\SignUpAuthentication;

use Hipper\Organization\OrganizationModel;
use Hipper\SignUpAuthentication\Exception\AuthenticationRequestForeignToOrganizationException;
use Hipper\SignUpAuthentication\SignUpAuthenticationModel;
use Hipper\SignUpAuthentication\SignUpAuthenticationRepository;
use Hipper\SignUpAuthentication\VerifySignUpAuthentication;
use Hipper\Validation\Exception\ValidationException;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class VerifySignUpAuthenticationTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $signUpAuthenticationRepository;
    private $verifySignUpAuthentication;

    public function setUp(): void
    {
        $this->signUpAuthenticationRepository = m::mock(SignUpAuthenticationRepository::class);

        $this->verifySignUpAuthentication = new VerifySignUpAuthentication(
            $this->signUpAuthenticationRepository
        );
    }

    /**
     * @test
     */
    public function verifyWithPhrase()
    {
        $authenticationRequestId = 'auth-req-uuid';
        $phrase = 'foo bar baz qux';

        $authenticationRequest = [
            'verification_phrase' => $phrase,
            'organization_id' => null,
        ];
        $this->createSignUpAuthenticationRepositoryExpectation([$authenticationRequestId], $authenticationRequest);

        $result = $this->verifySignUpAuthentication->verifyWithPhrase($authenticationRequestId, $phrase);
        $this->assertInstanceOf(SignUpAuthenticationModel::class, $result);
    }

    /**
     * @test
     */
    public function verifyWithPhraseInOrganization()
    {
        $authenticationRequestId = 'auth-req-uuid';
        $phrase = 'foo bar baz qux';
        $organizationId = 'org-uuid';
        $organization = OrganizationModel::createFromArray([
            'id' => $organizationId,
        ]);

        $authenticationRequest = [
            'verification_phrase' => $phrase,
            'organization_id' => $organizationId,
        ];
        $this->createSignUpAuthenticationRepositoryExpectation([$authenticationRequestId], $authenticationRequest);

        $result = $this->verifySignUpAuthentication->verifyWithPhrase($authenticationRequestId, $phrase, $organization);
        $this->assertInstanceOf(SignUpAuthenticationModel::class, $result);
    }

    /**
     * @test
     */
    public function authenticationRequestNotFound()
    {
        $authenticationRequestId = 'auth-req-uuid';
        $phrase = 'foo bar baz qux';

        $this->createSignUpAuthenticationRepositoryExpectation([$authenticationRequestId], null);

        $this->expectException(ValidationException::class);

        $this->verifySignUpAuthentication->verifyWithPhrase($authenticationRequestId, $phrase);
    }

    /**
     * @test
     */
    public function incorrectVerificationPhrase()
    {
        $authenticationRequestId = 'auth-req-uuid';
        $phrase = 'foo bar baz qux';

        $authenticationRequest = [
            'verification_phrase' => 'not the same phrase',
            'organization_id' => null,
        ];
        $this->createSignUpAuthenticationRepositoryExpectation([$authenticationRequestId], $authenticationRequest);

        $this->expectException(ValidationException::class);

        $this->verifySignUpAuthentication->verifyWithPhrase($authenticationRequestId, $phrase);
    }

    /**
     * @test
     */
    public function authenticationRequestForeginToOrganization()
    {
        $authenticationRequestId = 'auth-req-uuid';
        $phrase = 'foo bar baz qux';
        $organizationId = 'org-uuid';
        $organization = OrganizationModel::createFromArray([
            'id' => $organizationId,
        ]);

        $authenticationRequest = [
            'verification_phrase' => $phrase,
            'organization_id' => 'a-different-org-uuid',
        ];
        $this->createSignUpAuthenticationRepositoryExpectation([$authenticationRequestId], $authenticationRequest);

        $this->expectException(AuthenticationRequestForeignToOrganizationException::class);

        $this->verifySignUpAuthentication->verifyWithPhrase($authenticationRequestId, $phrase, $organization);
    }

    private function createSignUpAuthenticationRepositoryExpectation($args, $result)
    {
        $this->signUpAuthenticationRepository
            ->shouldReceive('findById')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
