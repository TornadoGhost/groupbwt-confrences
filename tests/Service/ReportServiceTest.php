<?php

namespace App\Tests\Service;

use App\Tests\AbstractTestCase;
use App\Entity\Conference;
use App\Repository\ReportRepository;
use App\Service\ConferenceService;
use App\Service\FileUploader;
use App\Service\ReportService;
use App\Tests\MockUtils;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReportServiceTest extends AbstractTestCase
{
    private ReportRepository $reportRepositoryMock;

    private FileUploader $fileUploaderMock;

    private ConferenceService $conferenceServiceMock;

    private EntityManager $entityManagerMock;

    private FlashBagInterface $flashBagMock;
    private Filesystem $filesystemMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reportRepositoryMock = $this->createMock(ReportRepository::class);
        $this->fileUploaderMock = $this->createMock(FileUploader::class);
        $this->conferenceServiceMock = $this->createMock(ConferenceService::class);
        $this->entityManagerMock = $this->createMock(EntityManager::class);
        $this->flashBagMock = $this->createMock(FlashBagInterface::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
    }

    public function testSaveWithoutFileSuccessfully(): void
    {
        $this->conferenceServiceMock->expects($this->once())
            ->method('findParticipantByUserId')
            ->with(1, 1)
            ->willReturn(null);

        $this->entityManagerMock->expects($this->never())->method('contains');
        $this->reportRepositoryMock->expects($this->never())->method('fileNameExist');
        $this->createMock(ReportService::class)->expects($this->never())
            ->method('deleteUploadedFile');
        $this->flashBagMock->expects($this->never())->method('add');

        $user = MockUtils::createUser();
        $this->setEntityId($user, 1);

        $conference = MockUtils::createConference();
        $this->setEntityId($conference, 1);

        $report = MockUtils::createReport()->setUser($user)->setConference($conference);
        $expected = MockUtils::createReport()->setConference($conference)->setUser($user);

        $this->assertFalse($conference->getUsers()->exists(function ($key, $user) {
            return $user->getId() === 1;
        }));
        $this->assertEquals($expected, $this->createReportService()->save($report, $conference, $user));
    }

    public function testSaveReportWithFileSuccessfully(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('getClientOriginalExtension')->willReturn('pptx');
        $uploadedFile->method('getClientOriginalName')->willReturn('test.pptx');

        $this->entityManagerMock->expects($this->once())
            ->method('contains')
            ->with(MockUtils::createReport()->setDocument('test.pptx'))
            ->willReturn(false);

        $this->fileUploaderMock->expects($this->once())
            ->method('upload')
            ->with($uploadedFile)
            ->willReturn('test.pptx');

        $this->conferenceServiceMock->expects($this->once())
            ->method('findParticipantByUserId')
            ->with(1, 1)
            ->willReturn([]);

        $report = MockUtils::createReport();
        $report->setDocument('test.pptx');

        $user = MockUtils::createUser();
        $this->setEntityId($user, 1);

        $conference = MockUtils::createConference();
        $this->setEntityId($conference, 1);

        $expected = MockUtils::createReport()->setDocument('test.pptx')
            ->setConference($conference)
            ->setUser($user);

        $this->reportRepositoryMock->expects($this->never())->method('fileNameExist');
        $this->createMock(ReportService::class)->expects($this->never())
            ->method('deleteUploadedFile');
        $this->flashBagMock->expects($this->never())->method('add');

        $this->assertFalse($conference->getUsers()->exists(function ($key, $user) {
            return $user->getId() === 1;
        }));
        $this->assertEquals($expected, $this->createReportService()->save($report, $conference, $user, $uploadedFile));
    }

    public function testSaveExistReportWithFileButFileUploadErrorWeb(): void
    {
        $user = MockUtils::createUser();
        $this->setEntityId($user, 1);

        $conference = MockUtils::createConference();
        $conference->addUser($user);
        $this->setEntityId($conference, 1);

        $report = MockUtils::createReport();
        $report->setDocument('test.pptx');
        $report->setUser($user);
        $report->setConference($conference);
        $this->setEntityId($report, 1);

        $document = ['/tmp/test.pptx'];

        $this->entityManagerMock->expects($this->once())
            ->method('contains')
            ->with($report)
            ->willReturn(true);

        $this->reportRepositoryMock->expects($this->once())
            ->method('fileNameExist')
            ->with($report->getId())
            ->willReturn($document);

        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('getClientOriginalExtension')->willReturn('pptx');
        $uploadedFile->method('getClientOriginalName')->willReturn('test.pptx');

        $this->fileUploaderMock->expects($this->once())
            ->method('upload')
            ->with($uploadedFile)
            ->willReturn(null);

        $this->flashBagMock->expects($this->once())
            ->method('add')
            ->with('file-error', 'File upload error. Try again later.');

        $this->conferenceServiceMock->expects($this->never())->method('findParticipantByUserId');
        $this->reportRepositoryMock->expects($this->never())->method('saveData');

        $this->assertNull($this->createReportService()->save($report, $conference, $user, $uploadedFile));
    }

    public function testSaveExistReportWithFileButFileUploadErrorApi(): void
    {
        $user = MockUtils::createUser();
        $this->setEntityId($user, 1);

        $conference = MockUtils::createConference();
        $conference->addUser($user);
        $this->setEntityId($conference, 1);

        $report = MockUtils::createReport();
        $report->setDocument('test.pptx');
        $report->setUser($user);
        $report->setConference($conference);
        $this->setEntityId($report, 1);

        $document = ['/tmp/test.pptx'];

        $this->entityManagerMock->expects($this->once())
            ->method('contains')
            ->with($report)
            ->willReturn(true);

        $this->reportRepositoryMock->expects($this->once())
            ->method('fileNameExist')
            ->with($report->getId())
            ->willReturn($document);

        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('getClientOriginalExtension')->willReturn('pptx');
        $uploadedFile->method('getClientOriginalName')->willReturn('test.pptx');

        $this->fileUploaderMock->expects($this->once())
            ->method('upload')
            ->with($uploadedFile)
            ->willReturn(null);

        $this->flashBagMock->expects($this->never())->method('add');
        $this->conferenceServiceMock->expects($this->never())->method('findParticipantByUserId');
        $this->reportRepositoryMock->expects($this->never())->method('saveData');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("File upload failed due to server error.");
        $this->expectExceptionCode(500);

        $this->createReportService()->save($report, $conference, $user, $uploadedFile, true);
    }

    public function testDeleteUploadedFileWhenFileExists(): void
    {
        $fileName = 'file-for-test.pptx';
        $targetDirectory = __DIR__ . '/../../public/uploads/reports';
        $filePath = $targetDirectory . '/' . $fileName;

        $this->fileUploaderMock->expects($this->once())
            ->method('getTargetDirectory')
            ->willReturn($targetDirectory);

        $this->filesystemMock->expects($this->once())
            ->method('exists')
            ->with($filePath)
            ->willReturn(true);
        $this->filesystemMock->expects($this->once())
            ->method('remove')
            ->with($filePath);

        $this->createReportService()->deleteUploadedFile($fileName);
    }

    public function testDeleteUploadedFileWhenFileDoesNotExist(): void
    {
        $fileName = 'file-for-test.pptx';
        $targetDirectory = __DIR__ . '/../../public/uploads/reports';
        $filePath = $targetDirectory . '/' . $fileName;

        $this->fileUploaderMock->expects($this->once())
            ->method('getTargetDirectory')
            ->willReturn($targetDirectory);

        $this->filesystemMock->expects($this->once())
            ->method('exists')
            ->with($filePath)
            ->willReturn(false);
        $this->filesystemMock->expects($this->never())
            ->method('remove');

        $this->createReportService()->deleteUploadedFile($fileName);
    }

    public function testDownloadFileReturnsStreamedResponse(): void
    {
        $fileName = 'file-for-test.pptx';
        $targetDirectory = __DIR__ . '/../../public/uploads/reports';
        $filePath = $targetDirectory . '/' . $fileName;

        $this->fileUploaderMock->expects($this->once())
            ->method('getTargetDirectory')
            ->willReturn($targetDirectory);

        chmod($filePath, 0666);
        $filePerms = fileperms($filePath);
        $this->assertTrue(
            ($filePerms & 0x0100) === 0x0100
            ||
            ($filePerms & 0x0020) === 0x0020
            || ($filePerms & 0x0004) === 0x0004
        );
        $this->assertTrue(file_exists($filePath), 'File must exist for this test');

        touch($filePath);

        $service = $this->createReportService();
        $response = $service->downloadFile($fileName);

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertEquals('application/octet-stream', $response->headers->get('Content-Type'));
        $this->assertEquals(
            "attachment; filename=\"{$fileName}\"",
            $response->headers->get('Content-Disposition')
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDownloadFileReturnsNotFoundException(): void
    {
        $fileName = 'file-for-test123.pptx';
        $targetDirectory = __DIR__ . '/../../public/uploads/reports';
        $filePath = $targetDirectory . '/' . $fileName;

        $this->fileUploaderMock->expects($this->once())
            ->method('getTargetDirectory')
            ->willReturn($targetDirectory);

        $this->assertFalse(file_exists($filePath), 'File must not exist for this test');

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('File not found');

        $this->createReportService()->downloadFile($fileName);
    }

    public function testDownloadFileThrowsRuntimeExceptionIfFileCannotBeOpened(): void
    {
        $fileName = 'file-for-test.pptx';
        $targetDirectory = __DIR__ . '/../../public/uploads/reports';
        $filePath = $targetDirectory . '/' . $fileName;

        $this->fileUploaderMock->expects($this->once())
            ->method('getTargetDirectory')
            ->willReturn($targetDirectory);

        $this->assertTrue(file_exists($filePath), 'File must exist for this test');

        chmod($filePath, 0222);
        $filePerms = fileperms($filePath);
        $this->assertFalse(
            ($filePerms & 0x0100) === 0x0100
            ||
            ($filePerms & 0x0020) === 0x0020
            || ($filePerms & 0x0004) === 0x0004
        );

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('File unavailable');

        $this->createReportService()->downloadFile($fileName);

        chmod($filePath, 0666);
    }

    public function testPrepareReportFiltersWhereStartTimeEqualEndTime(): void
    {
        $filters = [
            'start_time' => new \DateTime(),
            'end_time' => new \DateTime()
        ];
        $conference = (new Conference())
            ->setStartedAt(new \DateTime('2025-01-01 10:00'))
            ->setEndedAt(new \DateTime('2025-01-01 18:00'));

        $expected = ['start_time' => null, 'end_time' => null];

        $this->assertEquals($expected, $this->createReportService()->prepareReportFilters($filters, $conference));
    }

    public function testPrepareReportFiltersWhereOnlyStartTime(): void
    {
        $filters = [
            'start_time' => new \DateTime()
        ];
        $conference = (new Conference())
            ->setStartedAt(new \DateTime('2025-01-01 10:00'))
            ->setEndedAt(new \DateTime('2025-01-01 18:00'));

        $expected = [
            'start_time' => (new \DateTime($conference->getStartedAt()->format('Y-m-d')))->setTime(
                $filters['start_time']->format('H'),
                $filters['start_time']->format('i'),
                $filters['start_time']->format('s')
            )
        ];

        $this->assertEquals($expected, $this->createReportService()->prepareReportFilters($filters, $conference));
    }

    public function testPrepareReportFiltersWhereOnlyEndTime(): void
    {
        $filters = [
            'end_time' => new \DateTime()
        ];
        $conference = (new Conference())
            ->setStartedAt(new \DateTime('2025-01-01 10:00'))
            ->setEndedAt(new \DateTime('2025-01-01 18:00'));

        $expected = [
            'end_time' => (new \DateTime($conference->getStartedAt()->format('Y-m-d')))->setTime(
                $filters['end_time']->format('H'),
                $filters['end_time']->format('i'),
                $filters['end_time']->format('s')
            )
        ];

        $this->assertEquals($expected, $this->createReportService()->prepareReportFilters($filters, $conference));
    }

    public function testPrepareReportFiltersWhereStartAndEndTimeDifferent(): void
    {
        $filters = [
            'start_time' => new \DateTime(),
            'end_time' => (new \DateTime())->modify('+30 minutes')
        ];

        $conference = (new Conference())
            ->setStartedAt(new \DateTime('2025-01-01 10:00'))
            ->setEndedAt(new \DateTime('2025-01-01 18:00'));

        $expected = [
            'start_time' => (new \DateTime($conference->getStartedAt()->format('Y-m-d')))->setTime(
                $filters['start_time']->format('H'),
                $filters['start_time']->format('i'),
                $filters['start_time']->format('s')
            ),
            'end_time' => (new \DateTime($conference->getStartedAt()->format('Y-m-d')))->setTime(
                $filters['end_time']->format('H'),
                $filters['end_time']->format('i'),
                $filters['end_time']->format('s')
            )
        ];

        $this->assertEquals($expected, $this->createReportService()->prepareReportFilters($filters, $conference));
    }


    private function createReportService(): ReportService
    {
        return new ReportService(
            $this->reportRepositoryMock,
            $this->fileUploaderMock,
            $this->conferenceServiceMock,
            $this->entityManagerMock,
            $this->flashBagMock,
            $this->filesystemMock
        );
    }
}
