<?php
declare(strict_types=1);

namespace Hipper\Project;

class ProjectModel
{
    use \Hipper\ModelTrait;

    const FIELD_MAP = [
        'id' => 'id',
        'name' => 'name',
        'description' => 'description',
        'url_id' => 'urlId',
        'knowledgebase_id' => 'knowledgebaseId',
        'organization_id' => 'organizationId',
        'created' => 'created',
        'updated' => 'updated',
    ];

    private $id;
    private $name;
    private $description;
    private $urlId;
    private $knowledgebaseId;
    private $organizationId;
    private $created;
    private $updated;

    public static function createFromArray(array $array): ProjectModel
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

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setUrlId(string $urlId): void
    {
        $this->urlId = $urlId;
    }

    public function getUrlId(): string
    {
        return $this->urlId;
    }

    public function setKnowledgebaseId(string $knowledgebaseId): void
    {
        $this->knowledgebaseId = $knowledgebaseId;
    }

    public function getKnowledgebaseId(): string
    {
        return $this->knowledgebaseId;
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
