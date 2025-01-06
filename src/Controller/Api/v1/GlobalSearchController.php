<?php

namespace App\Controller\Api\v1;

use App\Service\GlobalSearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1")
 */
class GlobalSearchController extends AbstractController
{
    /**
     * @Route("/global-search", name="app_api_v1_globalsearch")
     */
    public function search(
        Request $request,
        GlobalSearchService $globalSearchService
    ): Response
    {
        $data = $globalSearchService->search($request);

        if (!$data) {
            return new Response('', 204);
        }

        return JsonResponse::fromJsonString($data);
    }
}
