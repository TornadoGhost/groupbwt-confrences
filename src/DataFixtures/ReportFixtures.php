<?php

namespace App\DataFixtures;

use App\Entity\Report;
use App\Repository\ConferenceRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ReportFixtures extends Fixture implements DependentFixtureInterface
{
    protected ConferenceRepository $conferenceRepository;
    public function __construct(
        ConferenceRepository $conferenceRepository
    )
    {
        $this->conferenceRepository = $conferenceRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 60; $i++ ) {
            $report = new Report();

            $report->setTitle($faker->sentence);
            $report->setStartedAt($faker->dateTimeBetween('now', '+8 hours'));
            $report->setEndedAt($faker->dateTimeBetween('+2 hours', '+10 hours'));
            $report->setDescription($faker->sentence);
            $report->setConference($this->conferenceRepository->getRandomConference());

            $manager->persist($report);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ConferenceFixtures::class,
        ];
    }
}
