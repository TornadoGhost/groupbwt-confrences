<?php

declare(strict_types=1);

namespace App\Controller\Api\v1;

use App\Service\GlobalSearchService;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1")
 */
class GlobalSearchController extends AbstractController
{
    /**
     * @Route("/global-search", name="app_api_v1_globalsearch", methods={"GET"})
     *
     * @OA\Parameter(
     *      name="page",
     *      in="query",
     *      description="Pagination page",
     *      @OA\Schema(type="integer"),
     *      example="1")
     * @OA\Response(response="200", description="Got paginated comments for specific report",
     *     @OA\JsonContent(
     *          type="object",
     *          @OA\Property(
     *              property="conferences",
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=0),
     *                  @OA\Property(property="title", type="string", example="string")
     *              )
     *          ),
     *          @OA\Property(
     *              property="reports",
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=0),
     *                  @OA\Property(property="title", type="string", example="string"),
     *                  @OA\Property(property="conference_id", type="integer", example=0)
     *              )
     *          )
     *      )
     * )
     * @OA\Response(response="500", description="Server error")
     */
    public function search(
        Request $request,
        GlobalSearchService $globalSearchService
    ): Response
    {
        $data = $globalSearchService->search($request);

        return $this->json($data, Response::HTTP_OK, [], ['groups' => ['global_search']]);
    }
}
