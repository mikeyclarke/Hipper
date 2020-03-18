<?Php
declare(strict_types=1);

namespace Hipper\SignUpAuthentication;

use Hipper\ModelTrait;

final class SignUpAuthenticationModel
{
    use ModelTrait;

    const FIELD_MAP = [
        'id' => 'id',
        'verification_phrase' => 'verificationPhrase',
        'email_address' => 'emailAddress',
        'name' => 'name',
        'encoded_password' => 'encodedPassword',
    ];

    private $id;
    private $verificationPhrase;
    private $emailAddress;
    private $name;
    private $encodedPassword;

    public static function createFromArray(array $array): self
    {
        $model = new static;
        $model->mapProperties($array);
        return $model;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setVerificationPhrase(string $verificationPhrase): void
    {
        $this->verificationPhrase = $verificationPhrase;
    }

    public function getVerificationPhrase(): string
    {
        return $this->verificationPhrase;
    }

    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setEncodedPassword(string $encodedPassword): void
    {
        $this->encodedPassword = $encodedPassword;
    }

    public function getEncodedPassword(): string
    {
        return $this->encodedPassword;
    }
}
