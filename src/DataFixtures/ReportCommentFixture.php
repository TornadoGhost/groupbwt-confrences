<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\ReportComment;
use App\Repository\ReportRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ReportCommentFixture extends Fixture implements DependentFixtureInterface
{
    protected UserRepository $userRepository;
    protected ReportRepository $reportRepository;

    public function __construct(
        UserRepository   $userRepository,
        ReportRepository $reportRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->reportRepository = $reportRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 1; $i <= 5000; $i++) {
            $comment = new ReportComment();
            $comment->setContent($faker->sentence);
            $comment->setUser($this->userRepository->getRandomUser());
            $comment->setReport($this->reportRepository->getRandomReport());

            $manager->persist($comment);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ReportFixtures::class
        ];
    }
}
