<?php
declare(strict_types=1);

namespace Hipper\Tests\Login;

use Hipper\Login\LoginSuccessRedirector;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class LoginSuccessRedirectorTest extends TestCase
{
    private $redirector;

    public function setUp(): void
    {
        $this->redirector = new LoginSuccessRedirector;
    }

    /**
     * @test
     */
    public function noRedirectInRequestBodyReturnsDefault()
    {
        $requestBody = [];
        $request = new Request([], $requestBody);

        $result = $this->redirector->generateUri($request);
        $this->assertEquals('/', $result);
    }

    /**
     * @test
     */
    public function nullRedirectInRequestBodyReturnsDefault()
    {
        $requestBody = [
            'redirect' => null,
        ];
        $request = new Request([], $requestBody);

        $result = $this->redirector->generateUri($request);
        $this->assertEquals('/', $result);
    }

    /**
     * @test
     */
    public function invalidRedirectInRequestBodyReturnsDefault()
    {
        $requestBody = [
            'redirect' => '<`not]a\\\valid//url.',
        ];
        $request = new Request([], $requestBody);

        $result = $this->redirector->generateUri($request);
        $this->assertEquals('/', $result);
    }

    /**
     * @test
     */
    public function absoluteRedirectInRequestBodyReturnsRedirect()
    {
        $requestBody = [
            'redirect' => 'https://acme.usehipper.com/foobar',
        ];
        $request = new Request([], $requestBody);

        $result = $this->redirector->generateUri($request);
        $this->assertEquals('/', $result);
    }

    /**
     * @test
     */
    public function noPathRedirectInRequestBodyReturnsRedirect()
    {
        $requestBody = [
            'redirect' => '?foo=bar',
        ];
        $request = new Request([], $requestBody);

        $result = $this->redirector->generateUri($request);
        $this->assertEquals('/', $result);
    }

    /**
     * @test
     */
    public function disallowedPathRedirectInRequestBodyReturnsRedirect()
    {
        $requestBody = [
            'redirect' => 'javascript://comment%0Aalert(1)/foo',
        ];
        $request = new Request([], $requestBody);

        $result = $this->redirector->generateUri($request);
        $this->assertEquals('/', $result);
    }

    /**
     * @test
     */
    public function validRedirectInRequestBodyReturnsRedirect()
    {
        $requestBody = [
            'redirect' => '/teams/engineering/docs/architecture/systems-design~bb239b51',
        ];
        $request = new Request([], $requestBody);

        $result = $this->redirector->generateUri($request);
        $this->assertEquals($requestBody['redirect'], $result);
    }
}
