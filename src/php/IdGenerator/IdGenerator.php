<?php
namespace hleo\IdGenerator;

class IdGenerator
{
    public function generate()
    {
        return uniqid();
    }
}
