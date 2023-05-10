<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Category;
use App\Entity\Transaction;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class TransactionService {

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) { }

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
     * @return Transaction
     */
    public function createTransaction(
        User $user,
        Category $category,
        string $createdAt,
        string $type,
        string $amount
    ): Transaction {

        $transaction = new Transaction();
        $transaction->setUser($user);
        $transaction->setCategory($category);

        $createdAt = new DateTimeImmutable($createdAt);
        $transaction->setCreatedAt($createdAt);
        $transaction->setAmount($amount);
        $transaction->setType($type);

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        return $transaction;
    }

}