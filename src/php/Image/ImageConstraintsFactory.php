<?php

declare(strict_types=1);

namespace Hipper\Image;

use RuntimeException;

class ImageConstraintsFactory
{
    public function __construct(
        private ?int $profileImageMaxWidth,
        private ?int $profileImageMaxHeight,
    ) {}

    public function create(string $usage): array
    {
        switch ($usage) {
            case 'profile_image':
                return [$this->profileImageMaxWidth, $this->profileImageMaxHeight];
            default:
                throw new RuntimeException('Unsupported usage');
        }
    }
}
