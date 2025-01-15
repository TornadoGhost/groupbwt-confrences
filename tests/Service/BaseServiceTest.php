<?php

namespace App\Tests\Service;

use App\Service\BaseService;
use App\Tests\AbstractTestCase;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

class BaseServiceTest extends AbstractTestCase
{
    public function testGetFormErrorsSuccessfully(): void
    {
        $mockForm = $this->createMock(FormInterface::class);
        $mockChild1 = $this->createMock(FormInterface::class);
        $mockChild2 = $this->createMock(FormInterface::class);

        $error1 = new FormError('Error message for field 1');
        $error2 = new FormError('Error message for field 2');

        $mockChild1
            ->method('getErrors')
            ->willReturn([$error1]);
        $mockChild2
            ->method('getErrors')
            ->willReturn([$error2]);

        $mockForm
            ->method('all')
            ->willReturn([
                'field1' => $mockChild1,
                'field2' => $mockChild2,
            ]);

        $this->assertEquals([
            'field1' => ['Error message for field 1'],
            'field2' => ['Error message for field 2'],
        ], (new BaseService())->getFormErrors($mockForm));
    }

    public function testGetFormErrorsEmptyArray(): void
    {
        $mockForm = $this->createMock(FormInterface::class);

        $mockForm
            ->method('all')
            ->willReturn([]);

        $this->assertEquals([], (new BaseService())->getFormErrors($mockForm));
    }
}
