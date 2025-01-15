<?php

namespace App\Tests\Service;

use App\Entity\Conference;
use App\Repository\ConferenceRepository;
use App\Service\ConferenceService;
use App\Tests\AbstractTestCase;
use App\Tests\MockUtils;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ConferenceServiceTest extends AbstractTestCase
{
    protected ConferenceRepository $conferenceRepositoryMock;
    protected UrlGeneratorInterface $urlGeneratorMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->conferenceRepositoryMock = $this->createMock(ConferenceRepository::class);
        $this->urlGeneratorMock = $this->createMock(UrlGeneratorInterface::class);
    }

    public function testGetAddressFromConferenceReturnArrayWithCoordinates(): void
    {
        $conference = MockUtils::createConference();
        $expects = ['latitude' => -80.866899, 'longitude' => -70.588201];

        $this->assertEquals($expects, $this->createConferenceService()->getAddressFromConference($conference));
    }

    public function testGetAddressFromConferenceReturnEmptyArray(): void
    {
        $conference = (new Conference())
            ->setTitle('Test Conference')
            ->setStartedAt(new \DateTime('2025-01-13 10:00'))
            ->setEndedAt(new \DateTime('2025-01-13 18:00'))
            ->setCountry('UA');
        $expects = [];

        $this->assertEquals($expects, $this->createConferenceService()->getAddressFromConference($conference));
    }

    public function testSaveFormChangesSuccessfully(): void
    {
        $conference = (new Conference())
        ->setTitle('Test Conference')
        ->setStartedAt(new \DateTime('2025-01-13 10:00'))
        ->setEndedAt(new \DateTime('2025-01-13 18:00'))
        ->setCountry('UA');

        $coordinates = ['latitude' => -80.866899, 'longitude' => -70.588201];

        $expects = MockUtils::createConference();
        $this->setEntityId($expects, 1);

        $this->conferenceRepositoryMock->expects($this->once())
            ->method('saveEditFormChanges')
            ->willReturn($expects);

        $this->assertEquals($expects, $this->createConferenceService()->saveFormChanges($conference, $coordinates));
    }
    public function testSaveFormChangesButUnexpectedValueException(): void
    {
        $conference = (new Conference())
            ->setTitle('Test Conference')
            ->setStartedAt(new \DateTime('2025-01-13 10:00'))
            ->setEndedAt(new \DateTime('2025-01-13 18:00'))
            ->setCountry('UA');

        $this->conferenceRepositoryMock->expects($this->never())
            ->method('saveEditFormChanges');

        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('Expected a non-empty array');

        $this->createConferenceService()->saveFormChanges($conference, []);
    }

    public function testSaveFormChangesButInvalidArgumentException(): void
    {
        $conference = (new Conference())
            ->setTitle('Test Conference')
            ->setStartedAt(new \DateTime('2025-01-13 10:00'))
            ->setEndedAt(new \DateTime('2025-01-13 18:00'))
            ->setCountry('UA');

        $this->conferenceRepositoryMock->expects($this->never())
            ->method('saveEditFormChanges');

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The array does not have "latitude" or "longitude"');

        $this->createConferenceService()->saveFormChanges($conference, ['randomKey' => 'randomValue']);
    }

    private function createConferenceService(): ConferenceService
    {
        return new ConferenceService($this->conferenceRepositoryMock, $this->urlGeneratorMock);
    }
}
