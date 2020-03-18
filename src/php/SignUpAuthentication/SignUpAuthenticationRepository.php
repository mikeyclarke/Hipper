<?php
declare(strict_types=1);

namespace Hipper\SignUpAuthentication;

use Hipper\SignUpAuthentication\Storage\StorageKeyComposer;
use Redis;

class SignUpAuthenticationRepository
{
    private StorageKeyComposer $storageKeyComposer;
    private Redis $redis;

    public function __construct(
        StorageKeyComposer $storageKeyComposer,
        Redis $redis
    ) {
        $this->storageKeyComposer = $storageKeyComposer;
        $this->redis = $redis;
    }

    public function findById(string $id): ?array
    {
        $key = $this->storageKeyComposer->compose($id);

        if ($this->redis->exists($key) === 0) {
            return null;
        }

        return $this->redis->hGetAll($key);
    }
}
