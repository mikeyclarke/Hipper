<?php

declare(strict_types=1);

namespace Hipper\File;

use Hipper\File\Exception\UnsupportedFileUsageException;

class StoragePathGenerator
{
    public function generate(string $usage, string $id, string $extension): string
    {
        $prefix = $this->getPrefix($usage);

        return sprintf('%s/%s.%s', $prefix, $id, $extension);
    }

    private function getPrefix(string $usage): string
    {
        switch ($usage) {
            case 'profile_image':
                return 'profile-image';
            default:
                throw new UnsupportedFileUsageException();
        }
    }
}
