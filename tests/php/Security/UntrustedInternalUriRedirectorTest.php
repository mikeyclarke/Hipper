<?php
declare(strict_types=1);

namespace Hipper\Tests\Security;

use Hipper\Security\UntrustedInternalUriRedirector;
use PHPUnit\Framework\TestCase;

class UntrustedInternalUriRedirectorTest extends TestCase
{
    private $redirector;

    public function setUp(): void
    {
        $this->redirector = new UntrustedInternalUriRedirector;
    }

    /**
     * @test
     */
    public function nullUntrustedUriReturnsDefault()
    {
        $untrustedUri = null;
        $defaultUri = '/';

        $result = $this->redirector->generateUri($untrustedUri, $defaultUri);
        $this->assertEquals($defaultUri, $result);
    }

    /**
     * @test
     */
    public function invalidUntrustedUriReturnsDefault()
    {
        $untrustedUri = '<`not]a\\\valid//url.';
        $defaultUri = '/';

        $result = $this->redirector->generateUri($untrustedUri, $defaultUri);
        $this->assertEquals($defaultUri, $result);
    }

    /**
     * @test
     */
    public function absoluteUntrustedUriReturnsDefault()
    {
        $untrustedUri = 'https://acme.usehipper.com/foobar';
        $defaultUri = '/';

        $result = $this->redirector->generateUri($untrustedUri, $defaultUri);
        $this->assertEquals($defaultUri, $result);
    }

    /**
     * @test
     */
    public function untrustedUriWithNoPathReturnsDefault()
    {
        $untrustedUri = '?foo=bar';
        $defaultUri = '/';

        $result = $this->redirector->generateUri($untrustedUri, $defaultUri);
        $this->assertEquals($defaultUri, $result);
    }

    /**
     * @test
     */
    public function untrustedUriWithDisallowedPathReturnsDefault()
    {
        $untrustedUri = 'javascript://comment%0Aalert(1)/foo';
        $defaultUri = '/';

        $result = $this->redirector->generateUri($untrustedUri, $defaultUri);
        $this->assertEquals($defaultUri, $result);
    }

    /**
     * @test
     */
    public function validAndSafeUntrustedUriReturnsUntrustedUri()
    {
        $untrustedUri = '/teams/engineering/docs/architecture/systems-design~bb239b51';
        $defaultUri = '/';

        $result = $this->redirector->generateUri($untrustedUri, $defaultUri);
        $this->assertEquals($untrustedUri, $result);
    }
}
