<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Entity\Conference;
use App\Form\ConferenceType;
use App\Tests\AbstractTypeTestCase;
class ConferenceTypeTest extends AbstractTypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'title' => 'Symfony Conference',
            'startedAt' => '2025-01-20T10:00',
            'endedAt' => '2025-01-20T18:00',
            'latitude' => 48.8566,
            'longitude' => 2.3522,
            'country' => 'FR',
        ];

        $conference = new Conference();
        $form = $this->factory->create(ConferenceType::class, $conference);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized(), 'Form is not synchronized.');
        $this->assertTrue($form->isValid(), 'Form is not valid.');

        $this->assertSame('Symfony Conference', $conference->getTitle());
        $this->assertEquals(new \DateTime('2025-01-20T10:00'), $conference->getStartedAt());
        $this->assertEquals(new \DateTime('2025-01-20T18:00'), $conference->getEndedAt());
        $this->assertSame('FR', $conference->getCountry());
    }

    public function testSubmitInvalidData(): void
    {
        $formData = [
            'title' => '',
            'startedAt' => 'invalid date',
            'endedAt' => '',
            'latitude' => null,
            'longitude' => null,
            'country' => '',
        ];

        $form = $this->factory->create(ConferenceType::class);
        $form->submit($formData);

        $this->assertFalse($form->isValid(), 'Form should not be valid.');
        $this->assertGreaterThan(
            0,
            count($form->getErrors(true, false)), 'Form should contain errors.'
        );
    }
}
