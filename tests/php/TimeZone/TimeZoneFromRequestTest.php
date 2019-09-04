<?php
declare(strict_types=1);

namespace Hipper\Tests\TimeZone;

use Hipper\TimeZone\TimeZoneFromRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class TimeZoneFromRequestTest extends TestCase
{
    private $timeZoneFromRequest;

    public function setUp(): void
    {
        $this->timeZoneFromRequest = new TimeZoneFromRequest;
    }

    /**
     * @test
     */
    public function noCookieReturnsDefault()
    {
        $requestCookies = [];
        $request = new Request([], [], [], $requestCookies);

        $result = $this->timeZoneFromRequest->get($request);
        $this->assertEquals('UTC', $result);
    }

    /**
     * @test
     */
    public function cookieWithUnsupportedTimeZoneReturnsDefault()
    {
        $cookieTimeZone = 'NOT A TIME ZONE';
        $requestCookies = ['tz' => $cookieTimeZone];
        $request = new Request([], [], [], $requestCookies);

        $result = $this->timeZoneFromRequest->get($request);
        $this->assertEquals('UTC', $result);
    }

    /**
     * @test
     */
    public function cookieWithSupportedTimeZoneIsReturned()
    {
        $cookieTimeZone = 'Europe/London';
        $requestCookies = ['tz' => $cookieTimeZone];
        $request = new Request([], [], [], $requestCookies);

        $result = $this->timeZoneFromRequest->get($request);
        $this->assertEquals($cookieTimeZone, $result);
    }
}
