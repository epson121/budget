<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Service\CategoryService;
use App\Validators\CategoryValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Annotations as OA;


class CategoriesController extends AbstractController
{

    public function __construct(
        private CategoryRepository $categoryRepository
        ) {}
    

    #[Route('/api/categories/{id}', name: 'api_categories_get', methods: ["GET"])]
    /**
     * @OA\Get(
     *     summary="Gets category information",
     *     description="Gets category information",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Category Id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     )
     * )
     */
    public function get(
        string $id
    ): JsonResponse {

        $category = $this->categoryRepository->findOneBy(['id' => $id]);

        if (!$category) {
            return $this->json(
                ['message' => 'Category with given ID does not exist.'],
                Response::HTTP_BAD_REQUEST
            );
        }
        
        return $this->json(
            [
                'id' => $category->getId(),
                'name' => $category->getName()
            ],
            Response::HTTP_OK
        );
    }

    #[Route('/api/categories/{id}', name: 'api_categories_delete', methods: ["DELETE"])]
    /**
     * @OA\Delete(
     *     summary="Delete category",
     *     description="Delete category",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Category Id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     )
     * )
     */
    public function delete(
        string $id
    ): JsonResponse {

        $category = $this->categoryRepository->findOneBy(['id' => $id, 'user' => $this->getUser()]);

        if (!$category) {
            return $this->json(
                ['message' => 'Category with given ID does not exist.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (!$category->getTransactions()->isEmpty()) {
            return $this->json(
                ['message' => 'Category has transactions, and can not be removed. Please delete transactions first.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->categoryRepository->remove($category, true);

        return $this->json(
            [
                'message' => 'Category deleted.',
            ],
            Response::HTTP_OK
        );
    }

    #[Route('/api/categories/{id}', name: 'api_categories_update', methods: ["PUT"])]
    /**
     * @OA\Put(
     *     summary="Updates a category",
     *     description="Updates a category",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Category Id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 example={"name": "New Category Name"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error"
     *     )
     * )
     */
    public function update(
        string $id,
        CategoryValidator $validator,
        Request $request
    ): JsonResponse {
        $category = $this->categoryRepository->findOneBy(['id' => $id, 'user' => $this->getUser()]);

        if (!$category) {
            return $this->json(['message' => 'Category with given ID does not exist.'], Response::HTTP_BAD_REQUEST);
        }

        $name = $request->get('name', '');

        $error = $validator->validateCategoryData([
            'name' => $name
        ]);

        if ($error) {
            return $this->json(['error' => $error->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $category->setName($name);
        $this->categoryRepository->save($category, true);

        return $this->json(
            [
                'message' => 'Successfully updated category',
                'id' => $category->getId(),
                'name' => $category->getName()
            ],
            Response::HTTP_OK
        );
    }

    #[Route('/api/categories', name: 'api_categories_get_all', methods: ["GET"])]
    /**
     * @OA\Get(
     *     summary="Gets category information",
     *     description="Gets category information",
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Success"
     *     )
     * )
     */
    public function getAll(): JsonResponse {

        $categories = $this->categoryRepository->findBy(['user' => $this->getUser()]);
        $data = [];

        foreach ($categories as $category) {
            $data[] = [
                'id' => $category->getId(),
                'firstName' => $category->getName(),
            ];
        }

        return $this->json(
            $data,
            Response::HTTP_OK
        );
    }

    

    #[Route('/api/categories', name: 'api_categories_create', methods: ["POST"])]
    /**
     * @OA\Post(
     *     summary="Creates a category",
     *     description="Creates a category",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 example={"name": "New Category"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Success"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Success"
     *     )
     * )
     */
    public function create(
        Request $request,
        CategoryValidator $validator,
        CategoryService $categoryService
    ): JsonResponse {

        $name = $request->get('name', '');

        $error = $validator->validateCategoryData([
            'name' => $name
        ]);

        if ($error) {
            return $this->json(['error' => $error->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        if ($categoryService->checkCategoryExists($name, $this->getUser())) {
            return $this->json(['error' => 'This category already exist'], Response::HTTP_BAD_REQUEST);
        }

        $category = $categoryService->createCategory($name, $this->getUser());

        return $this->json(
            [
                'message' => 'Successfully created category.',
                'id' => $category->getId(),
                'name' => $category->getName()
            ],
            Response::HTTP_CREATED
        );
    }
}
