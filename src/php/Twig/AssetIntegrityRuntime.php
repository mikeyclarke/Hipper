<?php
declare(strict_types=1);

namespace Hipper\Twig;

use Hipper\Asset\AssetIntegrity;
use RuntimeException;
use Twig\Extension\RuntimeExtensionInterface;

class AssetIntegrityRuntime implements RuntimeExtensionInterface
{
    private $assetIntegrity;

    public function __construct(
        AssetIntegrity $assetIntegrity
    ) {
        $this->assetIntegrity = $assetIntegrity;
    }

    public function getAssetIntegrity(string $path): ?string
    {
        return $this->assetIntegrity->getIntegrityHashesForAsset($path);
    }
}
