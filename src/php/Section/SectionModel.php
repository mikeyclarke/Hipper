<?php
declare(strict_types=1);

namespace Hipper\Section;

use Hipper\Knowledgebase\KnowledgebaseContentModelInterface;
use Hipper\ModelTrait;

final class SectionModel implements KnowledgebaseContentModelInterface
{
    use ModelTrait;

    const FIELD_MAP = [
        'id' => 'id',
        'name' => 'name',
        'description' => 'description',
        'url_slug' => 'urlSlug',
        'url_id' => 'urlId',
        'parent_section_id' => 'parentSectionId',
        'knowledgebase_id' => 'knowledgebaseId',
        'organization_id' => 'organizationId',
        'created' => 'created',
        'updated' => 'updated',
    ];

    private $id;
    private $name;
    private $description;
    private $urlSlug;
    private $urlId;
    private $parentSectionId;
    private $knowledgebaseId;
    private $organizationId;
    private $created;
    private $updated;

    public static function createFromArray(array $array): SectionModel
    {
        $model = new static;
        $model->mapProperties($array);
        return $model;
    }

    public function updateFromArray(array $array): void
    {
        $this->mapProperties($array);
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

    public function setUrlSlug(string $urlSlug): void
    {
        $this->urlSlug = $urlSlug;
    }

    public function getUrlSlug(): string
    {
        return $this->urlSlug;
    }

    public function setUrlId(string $urlId): void
    {
        $this->urlId = $urlId;
    }

    public function getUrlId(): string
    {
        return $this->urlId;
    }

    public function setParentSectionId(?string $parentSectionId): void
    {
        $this->parentSectionId = $parentSectionId;
    }

    public function getParentSectionId(): ?string
    {
        return $this->parentSectionId;
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
