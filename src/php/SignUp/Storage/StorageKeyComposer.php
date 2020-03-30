<?php
declare(strict_types=1);

namespace Hipper\SignUp\Storage;

class StorageKeyComposer
{
    private const KEY_NAMESPACE = 'sign_up_auth';

    public function compose(string $id): string
    {
        return sprintf('%s_%s', self::KEY_NAMESPACE, $id);
    }
}
