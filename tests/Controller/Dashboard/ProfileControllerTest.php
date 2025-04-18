<?php

namespace App\Tests\Controller\Dashboard;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProfileControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/dashboard/profile');

        self::assertResponseIsSuccessful();
    }
}
