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
    public function checkCategoryExists(string $name, User $user): bool
    {
        return !!$this->getCategoryByName($name, $user);
    }

    /**
     * @param string $name
     * @return ?Category
     */
    public function getCategoryByName(string $name, User $user): ?Category
    {
        $categoryRepository = $this->entityManager->getRepository(Category::class);
        return $categoryRepository->findOneBy(['name' => $name, 'user' => $user]);
    }

    /**
     * @param string $name
     * @return Category
     */
    public function createCategory(string $name, User $user): Category
    {
        $category = new Category();
        $category->setName($name);
        $category->setUser($user);

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }

}