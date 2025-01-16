<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Conference;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ConferenceFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 1; $i <= 20; $i++) {
            $conference = new Conference();
            $conference->setTitle($faker->sentence);
            $conference->setStartedAt(\DateTime::createFromFormat('Y-m-d H:i', "2024-11-$i 10:00"));
            $conference->setEndedAt(\DateTime::createFromFormat('Y-m-d H:i', "2024-11-$i 18:00"));
            $conference->setAddress([$faker->latitude, $faker->longitude]);
            $conference->setCountry($faker->countryCode);

            $manager->persist($conference);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
