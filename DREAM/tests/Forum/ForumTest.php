<?php

namespace App\Tests\Forum;

use App\Entity\Area;
use App\Entity\Farm;
use App\Entity\Farmer;
use App\Entity\Forum\Message;
use App\Entity\Forum\Thread;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ForumTest extends WebTestCase
{
    public function testThreadList(): void
    {
        $client = static::createClient();

        $em = $client->getContainer()->get('doctrine')->getManager();

        $area = new Area('Area 1');
        $em->persist($area);

        $farmer = new Farmer();
        $farmer->setName('John');
        $farmer->setSurname('Doe');
        $farmer->setEmail('example@example.com');
        $farmer->setBirthDate(new \DateTime());
        $farmer->setPassword('password');

        $farmer->setFarm((new Farm())->setArea($area)->setCity('City1')->setStreet('Street1'));

        $em->persist($farmer);

        $thread = new Thread($farmer, 'Thread 1');
        $thread->addMessage((new Message($farmer, 'Message Content')));

        $em->persist($thread);
        $em->flush();

        $client->loginUser($farmer);
        $client->request('GET', '/forum/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('table > tbody > tr:nth-child(1) > td:nth-child(2)', 'Thread 1');

        $client->request('GET', '/forum/thread/'.$thread->getId());
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.thread > div > div.card-body', 'Message Content');
    }
}
