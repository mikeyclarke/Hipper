<?php

declare(strict_types=1);

namespace Hipper\File;

use Hipper\File\Exception\UnsupportedFileUsageException;
use Hipper\IdGenerator\IdGenerator;

class StoragePathGenerator
{
    public function __construct(
        private IdGenerator $idGenerator,
    ) {}

    public function generate(string $usage, string $extension): string
    {
        $prefix = $this->getPrefix($usage);
        $id = $this->idGenerator->generate();

        return sprintf('%s/%s.%s', $prefix, $id, $extension);
    }

    private function getPrefix(string $usage): string
    {
        switch ($usage) {
            case 'profile_image':
                return 'profile-image';
            case 'profile_image_thumbnail':
                return 'profile-image-thumbnail';
            default:
                throw new UnsupportedFileUsageException();
        }
    }
}
