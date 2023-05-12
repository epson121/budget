<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use App\Event\UserCreatedEvent;
use App\Repository\TransactionRepository;
use App\Service\TransactionService;
use App\Validators\UserValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security;


class UserController extends AbstractController
{
    #[Route('/api/user/register', name: 'api_register', methods: ["POST"])]
    /**
     * @OA\Post(
     *     summary="Adds a new user",
     *     description="Adds a new user",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="username",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="confirm_password",
     *                     type="string"
     *                 ),
     *                 example={"username": "user123", "password": "password", "confirm_password": "password"}
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
    public function register(
        Request $request,
        UserValidator $userValidator,
        UserService $userService,
        EventDispatcherInterface $eventDispatcher
    ): JsonResponse {

        $username = $request->get('username', '');
        $password = $request->get('password', '');
        $confirmPassword = $request->get('confirm_password', '');
        
        $error = $userValidator->validateRegistrationData([
            'username' => $username,
            'password' => $password,
            'confirm_password' => $confirmPassword
        ]);

        if ($error) {
            return $this->json(['error' => $error->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        if ($userService->checkUserExist($username)) {
            return $this->json(['error' => 'This user already exist'], Response::HTTP_BAD_REQUEST);
        }

        $user = $userService->createUser($username, $password);

        $event = new UserCreatedEvent($user);
        $eventDispatcher->dispatch($event, UserCreatedEvent::NAME);

        return $this->json(
            [
                'message' => 'Successfully created user. You can now log in to obtain access token.',
                'id' => $user->getId(),
                'username' => $user->getUsername()
            ],
            Response::HTTP_CREATED
        );
    }

    #[Route('/api/user/status', name: 'api_user_status', methods: ["GET"])]
    /**
     * @OA\Get(
     *     summary="Gets user status",
     *     description="Gets user status",
     *     @OA\Response(
     *         response=201,
     *         description="Success"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error"
     *     )
     * )
     */
    public function status(): JsonResponse {

        /** @var User $user */
        $user = $this->getUser();

        return $this->json(
            [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'balance' => $user->getBalance()
            ],
            Response::HTTP_CREATED
        );
    }

    #[Route('/api/user/summary', name: 'api_user_summary', methods: ["GET"])]
    /**
     * @OA\Get(
     *     summary="Gets user budget summary",
     *     description="Gets user budget summary",
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
    public function summary(
        TransactionRepository $transactionRepository,
        TransactionService $transactionService,
        Request $request
    ): JsonResponse {

        /** @var User $user */
        $user = $this->getUser();

        $requestQuery = $request->query->all();

        // only filter by created_at
        foreach($requestQuery as $k => $v) {
            if ($k !== 'created_at') {
                unset($requestQuery[$k]);
            }
        }
        
        $requestQuery['user_id'] = ['eq' => $user->getId()];
        $qb = $transactionRepository->createQueryBuilder('t');
        try {
            $transactions = $transactionService->filterTransactions($qb, $requestQuery);
            $summary = $transactionService->getTransactionSummary($transactions);
        } catch (\Exception $e) {
            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
        
        return $this->json(
            $summary,
            Response::HTTP_CREATED
        );
    }
}
