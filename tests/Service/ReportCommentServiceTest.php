<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Repository\ReportCommentRepository;
use App\Service\ReportCommentService;
use App\Tests\AbstractTestCase;
use App\Tests\MockUtils;
use Symfony\Component\Form\FormFactoryInterface;
use Twig\Environment;

class ReportCommentServiceTest extends AbstractTestCase
{
    private ReportCommentRepository $reportCommentRepositoryMock;
    private FormFactoryInterface $formFactoryMock;
    private Environment $twigMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->reportCommentRepositoryMock = $this->createMock(ReportCommentRepository::class);
        $this->formFactoryMock = $this->createMock(FormFactoryInterface::class);
        $this->twigMock = $this->createMock(Environment::class);
    }

    public function testCreateReportCommentSuccessfullyReturnComment(): void
    {
        $expected = MockUtils::createComment()
            ->setUser(MockUtils::createUser())
            ->setReport(MockUtils::createReport());
        $this->setEntityId($expected, 1);

        $this->reportCommentRepositoryMock->expects($this->once())
            ->method('add')
            ->with(
                MockUtils::createComment()
                    ->setUser(MockUtils::createUser())
                    ->setReport(MockUtils::createReport()),
                true)
            ->willReturn($expected);

        $user = MockUtils::createUser();
        $report = MockUtils::createReport();
        $comment = MockUtils::createComment();

        $this->assertEquals(
            $expected,
            $this->createReportCommentService()->createReportComment($user, $report, $comment)
        );
    }

    public function testCreateReportCommentSuccessfullyReturnNull(): void
    {
        $this->reportCommentRepositoryMock->expects($this->once())
            ->method('add')
            ->with(
                MockUtils::createComment()
                    ->setUser(MockUtils::createUser())
                    ->setReport(MockUtils::createReport()))
            ->willReturn(null);

        $user = MockUtils::createUser();
        $report = MockUtils::createReport();
        $comment = MockUtils::createComment();

        $this->assertEquals(
            null,
            $this->createReportCommentService()->createReportComment($user, $report, $comment)
        );
    }

    private function createReportCommentService(): ReportCommentService
    {
        return new ReportCommentService(
            $this->reportCommentRepositoryMock,
            $this->formFactoryMock,
            $this->twigMock
        );
    }
}
