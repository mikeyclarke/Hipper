<?php

declare(strict_types=1);

namespace Hipper\File;

final class FileModel
{
    use \Hipper\ModelTrait;

    const FIELD_MAP = [
        'id' => 'id',
        'content_hash' => 'contentHash',
        'storage_path' => 'storagePath',
        'file_type' => 'fileType',
        'mime_type' => 'mimeType',
        'usage' => 'usage',
        'bytes' => 'bytes',
        'height' => 'height',
        'width' => 'width',
        'organization_id' => 'organizationId',
        'creator_id' => 'creatorId',
        'created' => 'created',
    ];

    private string $id;
    private string $contentHash;
    private string $storagePath;
    private string $fileType;
    private string $mimeType;
    private string $usage;
    private int $bytes;
    private ?int $height;
    private ?int $width;
    private string $organizationId;
    private string $creatorId;
    private string $created;

    public static function createFromArray(array $array): FileModel
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

    public function setContentHash($contentHash): void
    {
        $this->contentHash = $contentHash;
    }

    public function getContentHash(): string
    {
        return $this->contentHash;
    }

    public function setStoragePath(string $storagePath): void
    {
        $this->storagePath = $storagePath;
    }

    public function getStoragePath(): string
    {
        return $this->storagePath;
    }

    public function setFileType(string $fileType): void
    {
        $this->fileType = $fileType;
    }

    public function getFileType(): string
    {
        return $this->fileType;
    }

    public function isImage(): bool
    {
        return $this->fileType === 'image';
    }

    public function setMimeType(string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setUsage(string $usage): void
    {
        $this->usage = $usage;
    }

    public function getUsage(): string
    {
        return $this->usage;
    }

    public function setBytes(int $bytes): void
    {
        $this->bytes = $bytes;
    }

    public function getBytes(): int
    {
        return $this->bytes;
    }

    public function setHeight(?int $height): void
    {
        $this->height = $height;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setWidth(?int $width): void
    {
        $this->width = $width;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setOrganizationId(string $organizationId): void
    {
        $this->organizationId = $organizationId;
    }

    public function getOrganizationId(): string
    {
        return $this->organizationId;
    }

    public function setCreatorId(string $creatorId): void
    {
        $this->creatorId = $creatorId;
    }

    public function getCreatorId(): string
    {
        return $this->creatorId;
    }

    public function setCreated(string $created): void
    {
        $this->created = $created;
    }

    public function getCreated(): string
    {
        return $this->created;
    }
}
