<?php

namespace App\DataFixtures;

use App\Entity\Conference;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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
