<?php

namespace App\Controller;

use App\Service\CategoryService;
use App\Service\TransactionService;
use App\Validators\CategoryValidator;
use App\Event\TransactionCreatedEvent;
use App\Event\TransactionDeletedEvent;
use App\Event\TransactionUpdatedEvent;
use App\Repository\CategoryRepository;
use App\Validators\TransactionValidator;
use App\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Annotations as OA;


class TransactionsController extends AbstractController
{

    public function __construct(
        private TransactionRepository $transactionRepository
        ) {}
    

    #[Route('/api/transactions/{id}', name: 'api_transactions_get', methods: ["GET"])]
    /**
     * @OA\Get(
     *     summary="Gets transaction information",
     *     description="Gets transaction information",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Transaction Id",
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

        $transaction = $this->transactionRepository->findOneBy(['id' => $id, 'user' => $this->getUser()]);

        if (!$transaction) {
            return $this->json(['message' => 'Transaction with given ID does not exist.'], Response::HTTP_BAD_REQUEST);
        }
        
        return $this->json(
            [
                'id' => $transaction->getId(),
                'name' => $transaction->getAmount(),
                'created_at' => $transaction->getCreatedAt(),
                'amount' => $transaction->getAmount(),
                'description' => $transaction->getDescription(),
                'type' => $transaction->getType(),
                'category' => [
                    'id' => $transaction->getCategory()->getId(),
                    'name' => $transaction->getCategory()->getName()
                ]
            ],
            Response::HTTP_OK
        );
    }

    #[Route('/api/transactions/{id}', name: 'api_transactions_delete', methods: ["DELETE"])]
    /**
     * @OA\Delete(
     *     summary="Delete transaction",
     *     description="Delete transaction",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Transaction Id",
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
        string $id,
        EventDispatcherInterface $eventDispatcher
    ): JsonResponse {

        $transaction = $this->transactionRepository->findOneBy(['id' => $id]);

        if (!$transaction) {
            return $this->json(['message' => 'Transaction with given ID does not exist.'], Response::HTTP_BAD_REQUEST);
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
    /**
     * @OA\Put(
     *     summary="Updates a transaction",
     *     description="Updates a transaction",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Transaction Id",
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
     *                     property="type",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="category",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="amount",
     *                     type="number"
     *                 ),
     *                 @OA\Property(
     *                     property="created_at",
     *                     type="string",
     *                     format="datetime"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string"
     *                 ),
     *                 example={"type": "deposit", "amount": "120.20"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )
     * )
     */
    public function update(
        string $id,
        TransactionRepository $transactionRepository,
        TransactionValidator $validator,
        CategoryRepository $categoryRepository,
        EventDispatcherInterface $eventDispatcher,
        Request $request
    ): JsonResponse {

        $transaction = $transactionRepository->findOneBy(['id' => $id, 'user' => $this->getUser()]);

        if (!$transaction) {
            return $this->json(
                ['message' => 'Transaction with given ID does not exist.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $amount = $request->get('amount') ?? $transaction->getAmount();
        $type = $request->get('type') ?? $transaction->getType();
        $createdAt = $request->get('created_at') ?? $transaction->getCreatedAt()->format('Y-m-d H:i:s');
        $description = $request->get('description') ?? $transaction->getDescription();
        $categoryId = $request->get('category') ?? $transaction->getCategory()->getId();

        $error = $validator->validateTransactionData([
            'amount' => $amount,
            'type' => $type,
            'created_at' => $createdAt
        ]);

        if ($error) {
            return $this->json(['error' => $error->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $category = $categoryRepository->findOneBy(['id' => $categoryId, 'user' => $this->getUser()]);

        if (!$category) {
            return $this->json(['message' => 'Category with given ID does not exist.'], Response::HTTP_BAD_REQUEST);
        }

        $oldTransaction = clone $transaction;

        $transaction->setAmount($amount);
        $transaction->setCreatedAt(new \DateTimeImmutable($createdAt));
        $transaction->setCategory($category);
        $transaction->setDescription($description);
        $transaction->setType($type);

        $transactionRepository->save($transaction, true);

        $event = new TransactionUpdatedEvent($oldTransaction, $transaction);
        $eventDispatcher->dispatch($event, TransactionUpdatedEvent::NAME);

        return $this->json(
            [
                'message' => 'Successfully updated a transaction.',
                'id' => $transaction->getId()
            ],
            Response::HTTP_OK
        );
    }

    #[Route('/api/transactions', name: 'api_transactions_get_all', methods: ["GET"])]
    /**
     * @OA\Get(
     *     summary="Gets category information",
     *     description="Gets category information",
     *     @OA\Parameter(
     *         name="created_at",
     *         in="query",
     *         description="Created at",
     *         required=false,
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="created_at",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     example={"gte":"2022-05-11"}
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Type",
     *         required=false,
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="type",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     example={"eq":"expense"}
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="orderBy",
     *         in="query",
     *         description="Order by",
     *         required=false,
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="orderBy",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     example={"amount":"desc"}
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )
     * )
     */
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

        try {
            $transactions = $transactionService->filterTransactions($qb, $requestQuery);
        } catch (\Exception $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

        foreach ($transactions as $transaction) {
            $data[] = [
                'id' => $transaction->getId(),
                'name' => $transaction->getAmount(),
                'created_at' => $transaction->getCreatedAt(),
                'amount' => $transaction->getAmount(),
                'category' => [
                    'id' => $transaction->getCategory()->getId(),
                    'name' => $transaction->getCategory()->getName()
                ],
                'type' => $transaction->getType(),
                'description' => $transaction->getDescription()
            ];
        }

        return $this->json(
            $data,
            Response::HTTP_OK
        );
    }

    #[Route('/api/transactions', name: 'api_transactions_create', methods: ["POST"])]
    /**
     * @OA\Post(
     *     summary="Creates a transaction",
     *     description="Creates a transaction",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="type",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="category",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="amount",
     *                     type="number"
     *                 ),
     *                 @OA\Property(
     *                     property="created_at",
     *                     type="string",
     *                     format="datetime"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string"
     *                 ),
     *                 example={"type": "expense", "amount": "120.20", "category": 10, "created_at": "2023-05-11 08:00:00", "description": "food"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Success"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )
     * )
     */
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
        $description = $request->get('description', '');

        $error = $validator->validateTransactionData([
            'amount' => $amount,
            'type' => $type,
            'created_at' => $createdAt
        ]);

        if ($error) {
            return $this->json(['error' => $error->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $category = $categoryRepository->findOneBy(['id' => $categoryId, 'user' => $this->getUser()]);

        if (!$category) {
            return $this->json(['message' => 'Category with given ID does not exist.'], Response::HTTP_BAD_REQUEST);
        }

        $transaction = $transactionService->createTransaction(
            $this->getUser(),
            $category,
            $createdAt,
            $type,
            $amount,
            $description
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
