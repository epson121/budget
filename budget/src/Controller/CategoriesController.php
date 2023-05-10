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


class CategoriesController extends AbstractController
{

    public function __construct(
        private CategoryRepository $categoryRepository
        ) {}
    

    #[Route('/api/categories/{id}', name: 'api_categories_get', methods: ["GET"])]
    public function get(
        string $id
    ): JsonResponse {

        $category = $this->categoryRepository->findOneBy(['id' => $id]);

        if (!$category) {
            return $this->json(['message' => 'Category with given ID does not exist.']);
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
    public function delete(
        string $id
    ): JsonResponse {

        $category = $this->categoryRepository->findOneBy(['id' => $id, 'user' => $this->getUser()]);

        if (!$category) {
            return $this->json(['message' => 'Category with given ID does not exist.']);
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
    public function update(
        string $id,
        CategoryValidator $validator,
        Request $request
    ): JsonResponse {
        $category = $this->categoryRepository->findOneBy(['id' => $id, 'user' => $this->getUser()]);

        if (!$category) {
            return $this->json(['message' => 'Category with given ID does not exist.']);
        }

        $name = $request->get('name', '');

        $error = $validator->validateCategoryData([
            'name' => $name
        ]);

        if ($error) {
            return $this->json(['error' => $error->getMessage()], Response::HTTP_UNAUTHORIZED);
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
            return $this->json(['error' => $error->getMessage()], Response::HTTP_UNAUTHORIZED);
        }

        if ($categoryService->checkCategoryExists($name, $this->getUser())) {
            return $this->json(['error' => 'This category already exist'], Response::HTTP_UNAUTHORIZED);
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
