<?php

declare(strict_types=1);

namespace App\Controller\Api\v1;

use App\Service\GlobalSearchService;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GlobalSearchController extends AbstractController
{
    /**
     * @Route("/global-search", name="app_api_v1_globalsearch", methods={"GET"})
     */
    public function search(
        Request $request,
        GlobalSearchService $globalSearchService
    ): Response
    {
        return $this->json(
            $globalSearchService->search($request),
            Response::HTTP_OK,
            [],
            ['groups' => ['global_search']]
        );
    }
}
