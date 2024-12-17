<?php

namespace App\DataFixtures;

use App\Entity\Type;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use DateTime;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher
    )
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 5; $i++) {
            $admin = new User();
            $queryBuilder = $manager
                ->getRepository(Type::class)
                ->createQueryBuilder('t')
                ->orderBy('RAND()')
                ->setMaxResults(1);
            $randomType = $queryBuilder->getQuery()->getOneOrNullResult();

            if ($i === 0) {
                $admin->setRoles(['ROLE_ADMIN']);
                $admin->setEmail('admin@example.com');
                $admin->setType(null);
            } else {
                $admin->setEmail($faker->email);
                $admin->setType($randomType);
            }
            $admin->setFirstname($faker->firstName);
            $admin->setLastname($faker->lastName);
            $admin->setBirthdate(new DateTime($faker->date));
            $admin->setCountry($faker->countryCode);
            $admin->setPhone($faker->phoneNumber);
            $admin->setPassword($this->passwordHasher->hashPassword($admin, '12345678'));
            $manager->persist($admin);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TypeFixtures::class,
        ];
    }
}
