<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Form\ReportFiltersType;
use App\Tests\AbstractTypeTestCase;

class ReportFiltersTypeTest extends AbstractTypeTestCase
{
    private array $options;
    protected function setUp(): void
    {
        parent::setUp();

        $this->options = [
            'start_time' => new \DateTime('2025-01-15T10:00'),
            'end_time' => new \DateTime('2025-01-15T18:00'),
        ];
    }

    public function testSubmitValidData(): void
    {
        $startTime = new \DateTime('2025-01-15T10:00');
        $endTime = new \DateTime('2025-01-15T18:00');

        $formData = [
            'start_time' => [
                'hour' => 11,
                'minute' => 30,
            ],
            'end_time' => [
                'hour' => 12,
                'minute' => 30,
            ],
            'duration' => 30,
        ];

        $form = $this->factory->create(ReportFiltersType::class, null, $this->options);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized(), 'Form is not synchronized.');
        $this->assertTrue($form->isValid(), 'Form is not valid.');

        $this->assertEquals(
            range($startTime->format('H'), $endTime->format('H')),
            $form->get('start_time')->getConfig()->getOption('hours'),
            'Start time hours range is incorrect.'
        );

        $this->assertEquals(
            range($startTime->format('H'), $endTime->format('H')),
            $form->get('end_time')->getConfig()->getOption('hours'),
            'End time hours range is incorrect.'
        );

        $this->assertEquals('11:30', $form->get('start_time')->getData()->format('H:i'));
        $this->assertEquals('12:30', $form->get('end_time')->getData()->format('H:i'));
        $this->assertSame(30, $form->get('duration')->getData());
    }

    public function testSubmitInvalidData(): void
    {
        $formData = [
            'start_time' => '11:30',
            'end_time' => '12:30',
            'duration' => 'Very Very Long',
        ];

        $form = $this->factory->create(ReportFiltersType::class, null, $this->options);
        $form->submit($formData);

        $this->assertFalse($form->isValid(), 'Form should not be valid.');
        $this->assertGreaterThan(
            0,
            count($form->getErrors(true, false)), 'Form should contain errors.'
        );
    }
}
