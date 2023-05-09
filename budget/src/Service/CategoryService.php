<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CategoryService {

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) { }

       /**
     * @param string $name
     * @return bool
     */
    public function checkCategoryExists(string $name): bool
    {
        return !!$this->getCategoryByName($name);
    }

    /**
     * @param string $name
     * @return ?Category
     */
    public function getCategoryByName(string $name): ?Category
    {
        $categoryRepository = $this->entityManager->getRepository(Category::class);
        return $categoryRepository->findOneBy(['name' => $name]);
    }

    /**
     * @param string $name
     * @return Category
     */
    public function createCategory(string $name): Category
    {
        $category = new Category();
        $category->setName($name);

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }

}