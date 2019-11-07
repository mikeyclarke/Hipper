<?php
declare(strict_types=1);

namespace Hipper\Twig;

use Hipper\Twig\AssetIntegrityRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AssetIntegrityExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('asset_integrity', [AssetIntegrityRuntime::class, 'getAssetIntegrity']),
        ];
    }
}
