<?php

namespace App\Tests\Service;

use App\Service\FileUploader;
use App\Tests\AbstractTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\UnicodeString;

class FileUploaderServiceTest extends AbstractTestCase
{
    private SluggerInterface $sluggerMock;
    private ParameterBagInterface $parameterBagMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sluggerMock = $this->createMock(SluggerInterface::class);
        $this->parameterBagMock = $this->createMock(ParameterBagInterface::class);
    }

    public function testUploadSuccessful(): void
    {
        $this->sluggerMock->expects($this->once())
            ->method('slug')
            ->with('example-file')
            ->willReturn(new UnicodeString('example-file-slug'));

        $mockFile = $this->createMock(UploadedFile::class);
        $mockFile->expects($this->once())
            ->method('getClientOriginalName')
            ->willReturn('example-file.pptx');
        $mockFile->expects($this->once())
            ->method('guessExtension')
            ->willReturn('pptx');
        $mockFile->expects($this->once())
            ->method('move')
            ->with(
                '/public/uploads/reports',
                $this->matchesRegularExpression('/example-file-slug-[a-zA-Z0-9]+\.pptx/')
            );

        $this->assertMatchesRegularExpression(
            '/example-file-slug-[a-zA-Z0-9]+\.pptx/',
            $this->createFileUploaderService()->upload($mockFile)
        );
    }

    public function testUploadFailsOnFileException(): void
    {
        $this->sluggerMock->expects($this->once())
            ->method('slug')
            ->with('example-file')
            ->willReturn(new UnicodeString('example-file-slug'));

        $mockFile = $this->createMock(UploadedFile::class);
        $mockFile->expects($this->once())
            ->method('getClientOriginalName')
            ->willReturn('example-file.pptx');
        $mockFile->expects($this->once())
            ->method('guessExtension')
            ->willReturn('pptx');
        $mockFile->expects($this->once())
            ->method('move')
            ->willThrowException(new FileException());

        $this->assertNull($this->createFileUploaderService()->upload($mockFile));
    }

    public function createFileUploaderService(): FileUploader
    {
        return new FileUploader($this->sluggerMock, $this->parameterBagMock);
    }
}
