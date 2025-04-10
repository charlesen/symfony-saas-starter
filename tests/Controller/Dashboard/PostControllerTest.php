<?php

namespace App\Tests\Controller\Dashboard;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PostGeneratorControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/dashboard/post');

        self::assertResponseIsSuccessful();
    }
}
