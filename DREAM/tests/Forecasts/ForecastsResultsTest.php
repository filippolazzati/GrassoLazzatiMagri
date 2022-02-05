<?php

namespace App\Tests\Forecasts;

use App\Entity\Area;
use App\Entity\Farm;
use App\Entity\Farmer;
use App\Entity\Forum\Message;
use App\Entity\Forum\Thread;
use App\Entity\WeatherForecast;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class SuggestionsResultsTest
 * @package App\Tests\Suggestions
 *
 * This class aims at testing the forecasts provided by the WeatherForecastsController. This class aims to check
 * that the results are shown correctly to the end-user.
 */
class ForecastsResultsTest extends WebTestCase
{
    /**
     * This method checks that the results of the WeatherForecastsController are coherent with the
     * content of the database.
     */
    public function testForecastsResults(): void
    {
        // create client
        $client = static::createClient();
        //get entity manager
        $em = $client->getContainer()->get('doctrine')->getManager();

        // create area
        $area = new Area('Adilabad');
        $em->persist($area);
        // create farmer
        $farmer = new Farmer();
        $farmer->setName('John');
        $farmer->setSurname('Doe');
        $farmer->setEmail('example@example.com');
        $farmer->setBirthDate(new \DateTime());
        $farmer->setPassword($client->getContainer()->get(UserPasswordHasherInterface::class)->hashPassword($farmer, 'password'));
        $farmer->setFarm((new Farm())->setArea($area)->setCity('Adilabad')->setStreet('Street1'));
        $em->persist($farmer);
        // create an array of forecasts for the next 6 days in Adilabad
        $city = 'Adilabad';
        $forecasts = [
            new WeatherForecast(new \DateTime('+1 day'), $city, "sunny", 30, 22, 27, 0, 2.0, "n", 40, 1010),
            new WeatherForecast(new \DateTime('+2 day'), $city, "sunny", 28, 22, 26, 0, 2.5, "ne", 45, 1015),
            new WeatherForecast(new \DateTime('+3 day'), $city, "partially cloudy", 28, 20, 25, 0, 4, "n", 30, 1030),
            new WeatherForecast(new \DateTime('+4 day'), $city, "sunny", 29, 22, 26, 0, 3, "ne", 42, 1020),
            new WeatherForecast(new \DateTime('+5 day'), $city, "cloudy", 25, 19, 23, 5, 5, "e", 38, 990),
            new WeatherForecast(new \DateTime('+6 day'), $city, "rainy", 25, 17, 20, 0, 7, "e", 55, 1000),
        ];
        foreach ($forecasts as $forecast) {
            $em->persist($forecast);
        }

        // save data into the database
        $em->flush();

        // log the farmer into the system
        $client->loginUser($farmer);

        // create a request with kind 'fertilizer' and crop 'potatoes'
        $crawler = $client->request('GET', '/forecasts/');

        // select the button
        $buttonCrawlerNode = $crawler->selectButton('Go');

        // retrieve the Form object for the form belonging to this button
        $form = $buttonCrawlerNode->form();

        // set value on the form object
        $form['weather_forecasts[city]'] = 'Adilabad';

        // submit the Form object
        $client->submit($form);

        // check validity of the response
        self::assertResponseIsSuccessful();
        // check some random data in the table retrieved
        self::assertSelectorTextContains('table > tbody > tr:nth-child(1) > td:nth-child(2)', 'sunny');
        self::assertSelectorTextContains('table > tbody > tr:nth-child(2) > td:nth-child(3)', '28');
        self::assertSelectorTextContains('table > tbody > tr:nth-child(3) > td:nth-child(4)', '20');
        self::assertSelectorTextContains('table > tbody > tr:nth-child(4) > td:nth-child(6)', '0');
        self::assertSelectorTextContains('table > tbody > tr:nth-child(5) > td:nth-child(10)', '990');
    }
}
