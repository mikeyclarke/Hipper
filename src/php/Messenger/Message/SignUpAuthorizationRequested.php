<?php

declare(strict_types=1);

namespace Hipper\Messenger\Message;

class SignUpAuthorizationRequested implements HighPriorityAsyncMessageInterface
{
    private string $name;
    private string $emailAddress;
    private string $verificationPhrase;

    public function __construct(
        string $name,
        string $emailAddress,
        string $verificationPhrase
    ) {
        $this->name = $name;
        $this->emailAddress = $emailAddress;
        $this->verificationPhrase = $verificationPhrase;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function getVerificationPhrase(): string
    {
        return $this->verificationPhrase;
    }
}
