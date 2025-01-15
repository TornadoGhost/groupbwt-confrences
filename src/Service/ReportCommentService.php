<?php

namespace App\Service;

use App\Entity\Report;
use App\Entity\ReportComment;
use App\Form\ReportCommentType;
use App\Repository\ReportCommentRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Environment;

class ReportCommentService extends BaseService
{
    const MAX_PER_PAGE = 4;
    protected ReportCommentRepository $reportCommentRepository;
    protected FormFactoryInterface $formFactory;
    protected Environment $twig;

    public function __construct(
        ReportCommentRepository $reportCommentRepository,
        FormFactoryInterface    $formFactory,
        Environment             $twig
    )
    {
        $this->reportCommentRepository = $reportCommentRepository;
        $this->formFactory = $formFactory;
        $this->twig = $twig;
    }

    public function getAllCommentsByReportId(int $reportId): ?array
    {
        return $this->reportCommentRepository->getAllCommentsByReportId($reportId);
    }

    public function createReportComment(
        UserInterface $user,
        Report        $report,
        ReportComment $comment
    ): ?ReportComment
    {
        $comment->setUser($user);
        $comment->setReport($report);

        return $this->reportCommentRepository->add($comment, true);
    }

    public function updateComment(
        ReportComment $comment
    ): ?ReportComment
    {
        return $this->reportCommentRepository->add($comment, true);
    }

    public function removeComment(ReportComment $comment, bool $flush): void
    {
        $this->reportCommentRepository->remove($comment, $flush);
    }

    public function getCommentsByReportQueryBuilder(Report $report): QueryBuilder
    {
        return $this->reportCommentRepository->getCommentsByReportQueryBuilder($report);
    }

    public function getCommentsByPage(
        Report $report,
        int    $conferenceId,
        int    $page,
        int    $maxPerPage
    ): array
    {
        $qb = $this->getCommentsByReportQueryBuilder($report);

        $adapter = new QueryAdapter($qb);
        $pager = new Pagerfanta($adapter);
        $pager->setCurrentPage($page);
        $pager->setMaxPerPage($maxPerPage);

        $reportId = $report->getId();
        $comments = $pager->getCurrentPageResults();
        $commentsHtml = '';
        foreach ($comments as $comment) {
            $commentsHtml .= $this->twig->render('report/_comment_item.html.twig', [
                'comment' => $comment,
                'conferenceId' => $conferenceId,
                'reportId' => $reportId,
            ]);
        }

        $nextPage = ($pager->hasNextPage()) ? $page + 1 : null;

        return [
            'comments' => $commentsHtml,
            'nextPage' => $nextPage,
        ];
    }

    public function getCommentsPaginate(
        Report $report,
        int    $page,
        int    $maxPerPage
    ): ?array
    {
        $qb = $this->getCommentsByReportQueryBuilder($report);

        $adapter = new QueryAdapter($qb);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setCurrentPage($page);
        $pagerfanta->setMaxPerPage($maxPerPage);

        $comments = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $comments[] = $result;
        }

        return $comments;
    }

    public function save(ReportComment $entity, bool $flush = false): ?ReportComment
    {
        return $this->reportCommentRepository->add($entity, $flush);
    }
}
