<?php
declare(strict_types=1);

namespace Hipper\Organization;

use Hipper\Knowledgebase\KnowledgebaseOwnerModelInterface;

final class OrganizationModel implements KnowledgebaseOwnerModelInterface
{
    use \Hipper\ModelTrait;

    const DEFAULT_NAME = 'Unnamed Organization';
    const FIELD_MAP = [
        'id' => 'id',
        'knowledgebase_id' => 'knowledgebaseId',
        'name' => 'name',
        'subdomain' => 'subdomain',
        'approved_email_domain_signup_allowed' => 'approvedEmailDomainSignupAllowed',
        'approved_email_domains' => 'approvedEmailDomains',
        'created' => 'created',
        'updated' => 'updated',
    ];

    private $id;
    private $knowledgebaseId;
    private $name;
    private $subdomain;
    private $approvedEmailDomainSignupAllowed;
    private $approvedEmailDomains;
    private $created;
    private $updated;

    public static function createFromArray(array $array): OrganizationModel
    {
        $model = new static;
        $model->mapProperties($array);
        return $model;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setKnowledgebaseId(string $knowledgebaseId): void
    {
        $this->knowledgebaseId = $knowledgebaseId;
    }

    public function getKnowledgebaseId(): string
    {
        return $this->knowledgebaseId;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setSubdomain(?string $subdomain): void
    {
        $this->subdomain = $subdomain;
    }

    public function getSubdomain(): ?string
    {
        return $this->subdomain;
    }

    public function setApprovedEmailDomainSignupAllowed(bool $approvedEmailDomainSignupAllowed): void
    {
        $this->approvedEmailDomainSignupAllowed = $approvedEmailDomainSignupAllowed;
    }

    public function isApprovedEmailDomainSignupAllowed(): bool
    {
        return $this->approvedEmailDomainSignupAllowed;
    }

    public function setApprovedEmailDomains(?string $approvedEmailDomains): void
    {
        if (null !== $approvedEmailDomains) {
            $approvedEmailDomains = json_decode($approvedEmailDomains, true);
        }
        $this->approvedEmailDomains = $approvedEmailDomains;
    }

    public function getApprovedEmailDomains(): ?array
    {
        return $this->approvedEmailDomains;
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

    public function getUrlSlug(): ?string
    {
        return null;
    }
}
