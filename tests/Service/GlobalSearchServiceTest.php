<?php

namespace App\Tests\Service;

use App\Repository\ConferenceRepository;
use App\Repository\ReportRepository;
use App\Service\GlobalSearchService;
use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Request;

class GlobalSearchServiceTest extends AbstractTestCase
{
    protected ConferenceRepository $conferenceRepositoryMock;
    protected ReportRepository $reportRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->conferenceRepositoryMock = $this->createMock(ConferenceRepository::class);
        $this->reportRepositoryMock = $this->createMock(ReportRepository::class);
    }

    public function testGlobalSearchWithValidTitleAndType(): void
    {
        $request = new Request([
            'title' => 'Test',
            'type' => 'conference,report'
        ]);

        $title = $request->query->get('title');

        $this->conferenceRepositoryMock->expects($this->once())
            ->method('fullTextSearchByTitle')
            ->with($title)
            ->willReturn([
                ['id' => 1, 'title' => 'Conference Title Test'],
                ['id' => 2, 'title' => 'Conference Title Test2']
            ]);

        $this->reportRepositoryMock->expects($this->once())
            ->method('fullTextSearchByTitle')
            ->with($title)
            ->willReturn([
                ['id' => 1, 'title' => 'Report Title Test'],
                ['id' => 2, 'title' => 'Report Title Test2']
            ]);

        $expect = [
            'conferences' => [
                ['id' => 1, 'title' => 'Conference Title Test'],
                ['id' => 2, 'title' => 'Conference Title Test2']
            ],
            'reports' => [
                ['id' => 1, 'title' => 'Report Title Test'],
                ['id' => 2, 'title' => 'Report Title Test2']
            ]
        ];

        $this->assertEquals($expect, $this->createGlobalSearchService()->search($request));
    }

    public function testGlobalSearchWithOnlyConferenceType(): void
    {
        $request = new Request([
            'title' => 'Test',
            'type' => 'conference'
        ]);

        $this->conferenceRepositoryMock->expects($this->once())
            ->method('fullTextSearchByTitle')
            ->with($request->query->get('title'))
            ->willReturn([
                ['id' => 1, 'title' => 'Conference Title Test'],
                ['id' => 2, 'title' => 'Conference Title Test2']
            ]);

        $this->reportRepositoryMock->expects($this->never())
            ->method('fullTextSearchByTitle');

        $expect = [
            'conferences' => [
                ['id' => 1, 'title' => 'Conference Title Test'],
                ['id' => 2, 'title' => 'Conference Title Test2']
            ]
        ];

        $this->assertEquals($expect, $this->createGlobalSearchService()->search($request));
    }

    public function testGlobalSearchWithNoTitle(): void
    {
        $request = new Request([
            'type' => 'conference,report'
        ]);

        $this->conferenceRepositoryMock->expects($this->never())
            ->method('fullTextSearchByTitle');

        $this->reportRepositoryMock->expects($this->never())
            ->method('fullTextSearchByTitle');

        $expect = [];

        $this->assertEquals($expect, $this->createGlobalSearchService()->search($request));
    }

    public function createGlobalSearchService(): GlobalSearchService
    {
        return new GlobalSearchService($this->conferenceRepositoryMock, $this->reportRepositoryMock);
    }
}
