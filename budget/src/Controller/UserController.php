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


class UserController extends AbstractController
{
    #[Route('/api/register', name: 'api_register')]
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
            return $this->json(['error' => $error->getMessage()], Response::HTTP_UNAUTHORIZED);
        }

        if ($userService->checkUserExist($username)) {
            return $this->json(['error' => 'This user already exist'], Response::HTTP_UNAUTHORIZED);
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

    #[Route('/api/user/status', name: 'api_user_status')]
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

    #[Route('/api/user/summary', name: 'api_user_summary')]
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
            return $this->json([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
        

        return $this->json(
            $summary,
            Response::HTTP_CREATED
        );
    }
}
