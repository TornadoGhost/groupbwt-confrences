<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Form\ConferenceFiltersType;
use App\Tests\AbstractTypeTestCase;

class ConferenceFiltersTypeTest extends AbstractTypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'report_number' => 5,
            'start_date' => '2025-01-01T10:00',
            'end_date' => '2025-01-01T18:00',
            'is_available' => true,
        ];

        $form = $this->factory->create(ConferenceFiltersType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized(), 'Form is not synchronized.');
        $this->assertTrue($form->isValid(), 'Form is not valid.');

        $this->assertSame(5, $form->get('report_number')->getData());
        $this->assertEquals(new \DateTime('2025-01-01T10:00'), $form->get('start_date')->getData());
        $this->assertEquals(new \DateTime('2025-01-01T18:00'), $form->get('end_date')->getData());
        $this->assertTrue($form->get('is_available')->getData());
    }

    public function testSubmitInvalidData(): void
    {
        $formData = [
            'report_number' => -5,
            'start_date' => '',
            'end_date' => 'invalid date',
            'is_available' => '',
        ];

        $form = $this->factory->create(ConferenceFiltersType::class);
        $form->submit($formData);

        $this->assertFalse($form->isValid(), 'Form should not be valid.');
        $this->assertGreaterThan(
            0,
            count($form->getErrors(true, false)), 'Form should contain errors.'
        );
    }
}
