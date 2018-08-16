<?php
namespace hleo\IdGenerator;

use Ramsey\Uuid\Uuid;

class IdGenerator
{
    public function generate()
    {
        $id = Uuid::uuid1();
        return $id->toString();
    }
}
