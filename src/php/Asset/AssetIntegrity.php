<?php
declare(strict_types=1);

namespace Hipper\Asset;

use RuntimeException;

class AssetIntegrity
{
    private $manifestPath;
    private $manifestData;

    public function __construct(
        string $manifestPath
    ) {
        $this->manifestPath = $manifestPath;
    }

    public function getIntegrityHashesForAsset(string $path): ?string
    {
        return $this->getFromManifest($path) ?? '';
    }

    private function getFromManifest(string $path): ?string
    {
        if (null === $this->manifestData) {
            if (!file_exists($this->manifestPath)) {
                throw new RuntimeException(
                    sprintf('Integrity manifest file "%s" does not exist.', $this->manifestPath)
                );
            }

            $this->manifestData = json_decode(file_get_contents($this->manifestPath), true, JSON_THROW_ON_ERROR);
        }

        return $this->manifestData[$path] ?? null;
    }
}
