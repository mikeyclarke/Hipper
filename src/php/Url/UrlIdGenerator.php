<?php
declare(strict_types=1);

namespace Lithos\Url;

class UrlIdGenerator
{
    public function generate()
    {
        return bin2hex(random_bytes(4));
    }
}
