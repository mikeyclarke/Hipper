<?php
declare(strict_types=1);

namespace Hipper\SignUpAuthentication\Storage;

use Hipper\SignUpAuthentication\Storage\StorageKeyComposer;
use Redis;

class SignUpAuthenticationInserter
{
    private const TTL = 10800; // 3 hours

    private StorageKeyComposer $storageKeyComposer;
    private Redis $redis;

    public function __construct(
        StorageKeyComposer $storageKeyComposer,
        Redis $redis
    ) {
        $this->storageKeyComposer = $storageKeyComposer;
        $this->redis = $redis;
    }

    public function insert(
        string $id,
        string $verificationPhrase,
        string $emailAddress,
        string $name,
        string $encodedPassword,
        ?string $organizationId = null
    ): void {
        $key = $this->storageKeyComposer->compose($id);

        $this->redis->hMSet($key, [
            'verification_phrase' => $verificationPhrase,
            'email_address' => $emailAddress,
            'name' => $name,
            'encoded_password' => $encodedPassword,
            'organization_id' => $organizationId,
        ]);
        $this->redis->expire($key, self::TTL);
    }
}
