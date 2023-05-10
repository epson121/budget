<?php

namespace App\Controller;

use App\Service\CategoryService;
use App\Service\TransactionService;
use App\Validators\CategoryValidator;
use App\Event\TransactionCreatedEvent;
use App\Event\TransactionDeletedEvent;
use App\Repository\CategoryRepository;
use App\Validators\TransactionValidator;
use App\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class TransactionsController extends AbstractController
{

    public function __construct(
        private TransactionRepository $transactionRepository
        ) {}
    

    #[Route('/api/transactions/{id}', name: 'api_transactions_get', methods: ["GET"])]
    public function get(
        string $id
    ): JsonResponse {

        $transaction = $this->transactionRepository->findOneBy(['id' => $id, 'user' => $this->getUser()]);

        if (!$transaction) {
            return $this->json(['message' => 'Transaction with given ID does not exist.'], Response::HTTP_UNAUTHORIZED);
        }
        
        return $this->json(
            [
                'id' => $transaction->getId(),
                'name' => $transaction->getAmount(),
                'created_at' => $transaction->getCreatedAt(),
                'category' => [
                    'id' => $transaction->getCategory()->getId(),
                    'name' => $transaction->getCategory()->getName()
                ]
            ],
            Response::HTTP_OK
        );
    }

    #[Route('/api/transactions/{id}', name: 'api_transactions_delete', methods: ["DELETE"])]
    public function delete(
        string $id,
        EventDispatcherInterface $eventDispatcher
    ): JsonResponse {

        $transaction = $this->transactionRepository->findOneBy(['id' => $id]);

        if (!$transaction) {
            return $this->json(['message' => 'Transaction with given ID does not exist.'], Response::HTTP_UNAUTHORIZED);
        }

        $this->transactionRepository->remove($transaction, true);

        $event = new TransactionDeletedEvent($transaction);
        $eventDispatcher->dispatch($event, TransactionDeletedEvent::NAME);

        return $this->json(
            [
                'message' => 'Transaction deleted.',
            ],
            Response::HTTP_OK
        );
    }

    #[Route('/api/transactions/{id}', name: 'api_transactions_update', methods: ["PUT"])]
    public function update(
        string $id,
        TransactionRepository $transactionRepository,
        TransactionValidator $validator,
        CategoryRepository $categoryRepository,
        Request $request
    ): JsonResponse {

        $transaction = $transactionRepository->findOneBy(['id' => $id, 'user' => $this->getUser()]);

        if (!$transaction) {
            return $this->json(['message' => 'Transaction with given ID does not exist.']);
        }

        $amount = $request->get('amount') ?? $transaction->getAmount();
        $type = $request->get('type') ?? $transaction->getType();
        $createdAt = $request->get('created_at') ?? $transaction->getCreatedAt()->format('Y-m-d H:i:s');
        $categoryId = $request->get('category');

        $error = $validator->validateTransactionData([
            'amount' => $amount,
            'type' => $type,
            'created_at' => $createdAt
        ]);

        if ($error) {
            return $this->json(['error' => $error->getMessage()], Response::HTTP_UNAUTHORIZED);
        }

        $category = $categoryRepository->findOneBy(['id' => $categoryId]);

        if (!$category) {
            return $this->json(['message' => 'Category with given ID does not exist.'], Response::HTTP_UNAUTHORIZED);
        }

        $transaction->setAmount($amount);
        $transaction->setCreatedAt(new \DateTimeImmutable($createdAt));
        $transaction->setCategory($category);
        $transaction->setType($type);

        $transactionRepository->save($transaction, true);

        return $this->json(
            [
                'message' => 'Successfully updated a transaction.',
                'id' => $transaction->getId()
            ],
            Response::HTTP_OK
        );
    }

    #[Route('/api/transactions', name: 'api_transactions_get_all', methods: ["GET"])]
    public function getAll(
        TransactionService $transactionService,
        Request $request
    ): JsonResponse {

        $data = [];

        /** @var User $user */
        $user = $this->getUser();
        $requestQuery = $request->query->all();
        
        $requestQuery['user_id'] = ['eq' => $user->getId()];

        $qb = $this->transactionRepository->createQueryBuilder('t');

        $transactions = $transactionService->filterTransactions($qb, $requestQuery);

        foreach ($transactions as $transaction) {
            $data[] = [
                'id' => $transaction->getId(),
                'name' => $transaction->getAmount(),
                'created_at' => $transaction->getCreatedAt(),
                'amount' => $transaction->getAmount(),
                'category' => [
                    'id' => $transaction->getCategory()->getId(),
                    'name' => $transaction->getCategory()->getName()
                ]
            ];
        }

        return $this->json(
            $data,
            Response::HTTP_OK
        );
    }

    #[Route('/api/transactions', name: 'api_transactions_create', methods: ["POST"])]
    public function create(
        Request $request,
        TransactionValidator $validator,
        TransactionService $transactionService,
        CategoryRepository $categoryRepository,
        EventDispatcherInterface $eventDispatcher
    ): JsonResponse {

        $amount = $request->get('amount', '');
        $type = $request->get('type', '');
        $createdAt = $request->get('created_at', '') ;
        $categoryId = $request->get('category', '');

        $error = $validator->validateTransactionData([
            'amount' => $amount,
            'type' => $type,
            'created_at' => $createdAt
        ]);

        if ($error) {
            return $this->json(['error' => $error->getMessage()], Response::HTTP_UNAUTHORIZED);
        }

        $category = $categoryRepository->findOneBy(['id' => $categoryId, 'user' => $this->getUser()]);

        if (!$category) {
            return $this->json(['message' => 'Category with given ID does not exist.'], Response::HTTP_UNAUTHORIZED);
        }

        $transaction = $transactionService->createTransaction(
            $this->getUser(),
            $category,
            $createdAt,
            $type,
            $amount
        );

        $event = new TransactionCreatedEvent($transaction);
        $eventDispatcher->dispatch($event, TransactionCreatedEvent::NAME);

        return $this->json(
            [
                'message' => 'Successfully created a transaction.',
                'id' => $transaction->getId()
            ],
            Response::HTTP_CREATED
        );
    }
}
