<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Report;
use App\Repository\ConferenceRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ReportFixtures extends Fixture implements DependentFixtureInterface
{
    protected ConferenceRepository $conferenceRepository;
    protected UserRepository $userRepository;

    public function __construct(
        ConferenceRepository $conferenceRepository,
        UserRepository       $userRepository
    )
    {
        $this->conferenceRepository = $conferenceRepository;
        $this->userRepository = $userRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $conferences = $this->conferenceRepository->findAll();

        foreach ($conferences as $conference) {
            $conferenceHourStart = $conference->getStartedAt()->format('H');
            $conferenceHourEnd = $conference->getEndedAt()->format('H');
            $reportNumber = $conferenceHourEnd - $conferenceHourStart;
            $startHour = (int) $conferenceHourStart;
            $conferenceDateStart = $conference->getStartedAt()->format('Y-m-d');
            $conferenceDateEnd = $conference->getEndedAt()->format('Y-m-d');

            for ($i = 0; $i < $reportNumber; $i++) {
                $report = new Report();

                $report->setTitle($faker->sentence);
                $report->setStartedAt((new \DateTime($conferenceDateStart))->setTime($startHour, 0));
                $report->setEndedAt((new \DateTime($conferenceDateEnd))->setTime(++$startHour, 0));
                $report->setDescription($faker->sentence);
                $report->setConference($conference);
                $report->setUser($this->userRepository->getRandomAnnouncerUser());

                $manager->persist($report);
            }
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
