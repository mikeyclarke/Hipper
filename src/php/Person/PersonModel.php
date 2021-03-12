<?php
declare(strict_types=1);

namespace Hipper\Person;

final class PersonModel
{
    use \Hipper\ModelTrait;

    const FIELD_MAP = [
        'id' => 'id',
        'name' => 'name',
        'abbreviated_name' => 'abbreviatedName',
        'bio' => 'bio',
        'email_address' => 'emailAddress',
        'job_role_or_title' => 'jobRoleOrTitle',
        'url_id' => 'urlId',
        'username' => 'username',
        'onboarding_completed' => 'onboardingCompleted',
        'image_id' => 'imageId',
        'image_thumb_1x_id' => 'imageThumb1xId',
        'image_thumb_2x_id' => 'imageThumb2xId',
        'organization_id' => 'organizationId',
        'created' => 'created',
        'updated' => 'updated',
    ];

    private $id;
    private $name;
    private $abbreviatedName;
    private $bio;
    private $emailAddress;
    private $jobRoleOrTitle;
    private $username;
    private $urlId;
    private $onboardingCompleted;
    private $imageId;
    private $imageThumb1xId;
    private $imageThumb2xId;
    private $organizationId;
    private $created;
    private $updated;

    public static function createFromArray(array $array): PersonModel
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

    public function setBio(?string $bio): void
    {
        $this->bio = $bio;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function setJobRoleOrTitle(?string $jobRoleOrTitle): void
    {
        $this->jobRoleOrTitle = $jobRoleOrTitle;
    }

    public function getJobRoleOrTitle(): ?string
    {
        return $this->jobRoleOrTitle;
    }

    public function setUrlId(string $urlId): void
    {
        $this->urlId = $urlId;
    }

    public function getUrlId(): string
    {
        return $this->urlId;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setOnboardingCompleted(bool $onboardingCompleted): void
    {
        $this->onboardingCompleted = $onboardingCompleted;
    }

    public function isOnboardingCompleted(): bool
    {
        return $this->onboardingCompleted;
    }

    public function setImageId(?string $imageId): void
    {
        $this->imageId = $imageId;
    }

    public function getImageId(): ?string
    {
        return $this->imageId;
    }

    public function setImageThumb1xId(?string $imageThumb1xId): void
    {
        $this->imageThumb1xId = $imageThumb1xId;
    }

    public function getImageThumb1xId(): ?string
    {
        return $this->imageThumb1xId;
    }

    public function setImageThumb2xId(?string $imageThumb2xId): void
    {
        $this->imageThumb2xId = $imageThumb2xId;
    }

    public function getImageThumb2xId(): ?string
    {
        return $this->imageThumb2xId;
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
