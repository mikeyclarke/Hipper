<?php
declare(strict_types=1);

namespace Hipper\Tests\Messenger\MessageHandler;

use Hipper\Messenger\MessageHandler\SignUpAuthorizationRequestedHandler;
use Hipper\Messenger\Message\SignUpAuthorizationRequested;
use Hipper\TransactionalEmail\VerifyEmailAddressEmail;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class SignUpAuthorizationRequestedHandlerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $verifyEmailAddressEmail;
    private $handler;

    public function setUp(): void
    {
        $this->verifyEmailAddressEmail = m::mock(VerifyEmailAddressEmail::class);

        $this->handler = new SignUpAuthorizationRequestedHandler(
            $this->verifyEmailAddressEmail
        );
    }

    /**
     * @test
     */
    public function invoke()
    {
        $name = 'Miles Vorkosigan';
        $emailAddress = 'miles@example.com';
        $verificationPhrase = 'some nice verification phrase';
        $message = new SignUpAuthorizationRequested($name, $emailAddress, $verificationPhrase);

        $this->createVerifyEmailAddressEmailExpectation([$name, $emailAddress, $verificationPhrase]);

        $this->handler->__invoke($message);
    }

    private function createVerifyEmailAddressEmailExpectation($args)
    {
        $this->verifyEmailAddressEmail
            ->shouldReceive('send')
            ->once()
            ->with(...$args);
    }
}
