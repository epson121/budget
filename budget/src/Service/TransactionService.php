<?php

namespace App\Service;

use App\Entity\User;
use DateTimeImmutable;
use App\Entity\Category;
use App\Entity\Transaction;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Maldoinc\Doctrine\Filter\DoctrineFilter;
use Maldoinc\Doctrine\Filter\Action\ActionList;
use Symfony\Component\HttpFoundation\JsonResponse;
use Maldoinc\Doctrine\Filter\Reader\ExposedFieldsReader;
use Maldoinc\Doctrine\Filter\Provider\PresetFilterProvider;
use Maldoinc\Doctrine\Filter\Reader\AttributeReader\NativeAttributeReader;

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

    public function filterTransactions(QueryBuilder $queryBuilder, $requestQuery)
    {
        $fieldReader = new ExposedFieldsReader(new NativeAttributeReader());

        $filter = new DoctrineFilter($queryBuilder, $fieldReader, [new PresetFilterProvider()]);
        $actions = ActionList::fromArray(
            data: $requestQuery,
            
            // The key under which to look for sorting actions
            orderByKey: 'orderBy', 
            
            simpleEquality: true 
        );
        
        $filter->apply($actions);

        return $queryBuilder->getQuery()->getResult();
    }

}