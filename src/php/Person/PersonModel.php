<?php
declare(strict_types=1);

namespace Hipper\Person;

class PersonModel
{
    private $id;
    private $name;
    private $abbreviatedName;
    private $emailAddress;
    private $emailAddressVerified;
    private $onboardingCompleted;
    private $organizationId;
    private $created;
    private $updated;

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setAbbreviatedName(string $abbreviatedName): void
    {
        $this->abbreviatedName = $abbreviatedName;
    }

    public function getAbbreviatedName(): string
    {
        return $this->abbreviatedName;
    }

    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function setEmailAddressVerified(bool $emailAddressVerified): void
    {
        $this->emailAddressVerified = $emailAddressVerified;
    }

    public function getEmailAddressVerified(): bool
    {
        return $this->emailAddressVerified;
    }

    public function setOnboardingCompleted(bool $onboardingCompleted): void
    {
        $this->onboardingCompleted = $onboardingCompleted;
    }

    public function isOnboardingCompleted(): bool
    {
        return $this->onboardingCompleted;
    }

    public function setOrganizationId(string $organizationId): void
    {
        $this->organizationId = $organizationId;
    }

    public function getOrganizationId(): string
    {
        return $this->organizationId;
    }

    public function setCreated(string $created): void
    {
        $this->created = $created;
    }

    public function getCreated(): string
    {
        return $this->created;
    }

    public function setUpdated(string $updated): void
    {
        $this->updated = $updated;
    }

    public function getUpdated(): string
    {
        return $this->updated;
    }
}
