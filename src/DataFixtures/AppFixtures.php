<?php

namespace App\DataFixtures;

use App\Entity\Type;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $userTypes = ['listener', 'participant'];
        foreach($userTypes as $type) {
            $userType = new Type();
            $userType->setName($type);
            $manager->persist($userType);
        }

        $admin = new User();
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setEmail('admin@example.com ');
        $admin->setPassword('12345678');
        $admin->setType(null);
        $admin->setFirstname('Bohdan');
        $admin->setLastname('Babiak');
        $admin->setBirthdate(new \DateTime('1997-04-29'));
        $admin->setCountry('Ukraine');
        $admin->setPhone('+380984357294');
        $manager->persist($admin);

        $manager->flush();
    }
}
