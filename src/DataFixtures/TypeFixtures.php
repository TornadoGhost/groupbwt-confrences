<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TypeFixtures extends Fixture
{
    private const LISTENER = 'Listener';
    private const ANNOUNCER = 'Announcer';
    public function load(ObjectManager $manager): void
    {
        $userTypes = [self::LISTENER, self::ANNOUNCER];
        foreach ($userTypes as $type) {
            $userType = new Type();
            $userType->setName($type);
            $manager->persist($userType);
        }

        $manager->flush();
    }
}
