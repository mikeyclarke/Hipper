<?php
declare(strict_types=1);

namespace Hipper\Invite;

use DateTime;
use Hipper\ModelTrait;
use RuntimeException;

final class InviteModel
{
    use ModelTrait;

    const FIELD_MAP = [
        'id' => 'id',
        'email_address' => 'emailAddress',
        'invited_by' => 'invitedBy',
        'organization_id' => 'organizationId',
        'token' => 'token',
        'sent' => 'sent',
        'expires' => 'expires',
        'created' => 'created',
        'updated' => 'updated',
    ];

    private $id;
    private $emailAddress;
    private $invitedBy;
    private $organizationId;
    private $token;
    private $sent;
    private $expires;
    private $created;
    private $updated;

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

    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function setInvitedBy(string $invitedBy): void
    {
        $this->invitedBy = $invitedBy;
    }

    public function getInvitedBy(): string
    {
        return $this->invitedBy;
    }

    public function setOrganizationId(string $organizationId): void
    {
        $this->organizationId = $organizationId;
    }

    public function getOrganizationId(): string
    {
        return $this->organizationId;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setSent(bool $sent): void
    {
        $this->sent = $sent;
    }

    public function isSent(): bool
    {
        return $this->sent;
    }

    public function setExpires(string $expires): void
    {
        $this->expires = $expires;
    }

    public function getExpires(): string
    {
        return $this->expires;
    }

    public function hasExpired(): bool
    {
        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $this->expires);
        if (false === $dateTime) {
            throw new RuntimeException('DateTime could not be created from format');
        }

        return $dateTime < new DateTime('now');
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
