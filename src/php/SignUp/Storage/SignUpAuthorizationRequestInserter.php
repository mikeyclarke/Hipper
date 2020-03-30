<?php
declare(strict_types=1);

namespace Hipper\SignUp\Storage;

use Hipper\SignUp\Storage\StorageKeyComposer;
use Redis;

class SignUpAuthorizationRequestInserter
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
        ?string $organizationId = null,
        ?string $organizationName = null
    ): void {
        $key = $this->storageKeyComposer->compose($id);

        $this->redis->hMSet($key, [
            'verification_phrase' => $verificationPhrase,
            'email_address' => $emailAddress,
            'name' => $name,
            'encoded_password' => $encodedPassword,
            'organization_id' => $organizationId,
            'organization_name' => $organizationName,
        ]);
        $this->redis->expire($key, self::TTL);
    }
}
