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

class ReportCommentService
{
    const MAX_PER_PAGE = 5;
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
        Request        $request,
        UserInterface  $user,
        Report         $report,
        ?ReportComment $comment = null
    ): ?FormInterface
    {
        if (!$comment) {
            $comment = new ReportComment();
        }

        $commentForm = $this->formFactory->create(ReportCommentType::class, $comment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment->setUser($user);
            $comment->setReport($report);
            $this->reportCommentRepository->add($comment, true);

            return null;
        }

        return $commentForm;
    }

    public function updateComment(
        Request        $request,
        ?ReportComment $comment
    ): ?FormInterface
    {
        $commentForm = $this->formFactory->create(ReportCommentType::class, $comment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $this->reportCommentRepository->add($comment, true);

            return null;
        }

        return $commentForm;
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
}
