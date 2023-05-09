<?php

namespace App\Controller;

use App\Service\UserService;
use App\Validators\UserValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class UserController extends AbstractController
{
    #[Route('/api/register', name: 'api_register')]
    public function register(
        Request $request,
        UserValidator $userValidator,
        UserService $userService
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

        return $this->json(
            [
                'message' => 'Successfully created user. You can now log in to obtain access token.',
                'id' => $user->getId(),
                'username' => $user->getUsername()
            ],
            Response::HTTP_CREATED
        );
    }
}
