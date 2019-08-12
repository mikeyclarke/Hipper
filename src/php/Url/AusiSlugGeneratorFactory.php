<?php
declare(strict_types=1);

namespace Hipper\Url;

use Ausi\SlugGenerator\SlugGenerator;

class AusiSlugGeneratorFactory
{
    public function create(): SlugGenerator
    {
        return new SlugGenerator;
    }
}
