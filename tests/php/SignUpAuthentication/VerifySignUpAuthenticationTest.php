<?php
declare(strict_types=1);

namespace Hipper\Tests\SignUpAuthentication;

use Hipper\SignUpAuthentication\Exception\AuthenticationRequestNotFoundException;
use Hipper\SignUpAuthentication\Exception\IncorrectVerificationPhraseException;
use Hipper\SignUpAuthentication\SignUpAuthenticationModel;
use Hipper\SignUpAuthentication\SignUpAuthenticationRepository;
use Hipper\SignUpAuthentication\VerifySignUpAuthentication;
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
        ];
        $this->createSignUpAuthenticationRepositoryExpectation([$authenticationRequestId], $authenticationRequest);

        $result = $this->verifySignUpAuthentication->verifyWithPhrase($authenticationRequestId, $phrase);
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

        $this->expectException(AuthenticationRequestNotFoundException::class);

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
        ];
        $this->createSignUpAuthenticationRepositoryExpectation([$authenticationRequestId], $authenticationRequest);

        $this->expectException(IncorrectVerificationPhraseException::class);

        $this->verifySignUpAuthentication->verifyWithPhrase($authenticationRequestId, $phrase);
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
