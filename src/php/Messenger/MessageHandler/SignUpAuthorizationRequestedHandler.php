<?php
declare(strict_types=1);

namespace Hipper\Messenger\MessageHandler;

use Hipper\Messenger\Message\SignUpAuthorizationRequested;
use Hipper\TransactionalEmail\VerifyEmailAddressEmail;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SignUpAuthorizationRequestedHandler implements MessageHandlerInterface
{
    private VerifyEmailAddressEmail $verifyEmailAddressEmail;

    public function __construct(
        VerifyEmailAddressEmail $verifyEmailAddressEmail
    ) {
        $this->verifyEmailAddressEmail = $verifyEmailAddressEmail;
    }

    public function __invoke(SignUpAuthorizationRequested $message): void
    {
        $this->verifyEmailAddressEmail->send(
            $message->getName(),
            $message->getEmailAddress(),
            $message->getVerificationPhrase()
        );
    }
}
