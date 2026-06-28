<?php

namespace Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CaptchaControllerTest extends WebTestCase
{
    public function testImageRouteReturnsPng(): void
    {
        $client = static::createClient();
        $client->request('GET', '/captcha/image');

        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('image/png', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $this->assertSame("\x89PNG", substr((string) $response->getContent(), 0, 4));
    }
}
