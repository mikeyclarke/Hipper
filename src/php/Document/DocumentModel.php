<?php
declare(strict_types=1);

namespace Hipper\Document;

use Hipper\Knowledgebase\KnowledgebaseContentModelInterface;

final class DocumentModel implements KnowledgebaseContentModelInterface
{
    use \Hipper\ModelTrait;

    const FIELD_MAP = [
        'id' => 'id',
        'name' => 'name',
        'description' => 'description',
        'deduced_description' => 'deducedDescription',
        'content' => 'content',
        'url_slug' => 'urlSlug',
        'url_id' => 'urlId',
        'knowledgebase_id' => 'knowledgebaseId',
        'organization_id' => 'organizationId',
        'section_id' => 'sectionId',
        'created_by' => 'createdBy',
        'last_updated_by' => 'lastUpdatedBy',
        'created' => 'created',
        'updated' => 'updated',
    ];

    private $id;
    private $name;
    private $description;
    private $deducedDescription;
    private $content;
    private $urlSlug;
    private $urlId;
    private $knowledgebaseId;
    private $organizationId;
    private $sectionId;
    private $createdBy;
    private $lastUpdatedBy;
    private $created;
    private $updated;

    public static function createFromArray(array $array): DocumentModel
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

    public function setDeducedDescription(?string $deducedDescription): void
    {
        $this->deducedDescription = $deducedDescription;
    }

    public function getDeducedDescription(): ?string
    {
        return $this->deducedDescription;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getContent(): ?string
    {
        return $this->content;
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

    public function setSectionId(?string $sectionId): void
    {
        $this->sectionId = $sectionId;
    }

    public function getSectionId(): ?string
    {
        return $this->sectionId;
    }

    public function setCreatedBy(string $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function getCreatedBy(): string
    {
        return $this->createdBy;
    }

    public function setLastUpdatedBy(string $lastUpdatedBy): void
    {
        $this->lastUpdatedBy = $lastUpdatedBy;
    }

    public function getLastUpdatedBy(): string
    {
        return $this->lastUpdatedBy;
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
