<?php

namespace App\Tests;

use App\Entity\Conference;
use App\Entity\Report;
use App\Entity\ReportComment;
use App\Entity\User;

class MockUtils
{
    public static function createReport(): Report
    {
        return (new Report())
            ->setTitle('Test Report')
            ->setStartedAt(new \DateTime('2025-01-13 11:00'))
            ->setEndedAt(new \DateTime('2025-01-13 11:30'))
            ->setDescription("Test description");
    }

    public static function createUser(): User
    {
        return (new User())
            ->setEmail('test.email@gmail.com')
            ->setRoles(['ROLE_ANNOUNCER'])
            ->setPassword('12345678')
            ->setFirstname('Firstname')
            ->setLastname('Lastname')
            ->setBirthdate((new \DateTime('1997-04-29')))
            ->setCountry('UA')
            ->setPhone('+38098000001');
    }

    public static function createConference(): Conference
    {
        return (new Conference())
            ->setTitle('Test Conference')
            ->setStartedAt(new \DateTime('2025-01-13 10:00'))
            ->setEndedAt(new \DateTime('2025-01-13 18:00'))
            ->setCountry('UA')
            ->setAddress([-80.866899, -70.588201]);
    }

    public static function createComment(): ReportComment
    {
        return (new ReportComment())->setContent('Test Content');
    }
}
