<?php

namespace App\DataFixtures;

use App\Entity\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TypeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $userTypes = ['Listener', 'Announcer'];
        foreach($userTypes as $type) {
            $userType = new Type();
            $userType->setName($type);
            $manager->persist($userType);
        }

        $manager->flush();
    }
}
