<?php
declare(strict_types=1);

namespace Lithos\Organization;

class OrganizationModel
{
    private $id;
    private $name;
    private $subdomain;
    private $created;
    private $updated;

    public function setId($id): void
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

    public function setSubdomain(?string $subdomain): void
    {
        $this->subdomain = $subdomain;
    }

    public function getSubdomain(): ?string
    {
        return $this->subdomain;
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