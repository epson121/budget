<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService {

    /**
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordHasherInterface $passwordHasher
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) { }

    /**
     * @param string $username
     * @return bool
     */
    public function checkUserExist(string $username): bool
    {
        return !!$this->getUserByUsername($username);
    }

    /**
     * @param string $username
     * @param string $password
     * @return User
     */
    public function createUser(string $username, string $password): User
    {
        $user = new User();
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setUsername($username);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @param string $username
     * @return ?User
     */
    public function getUserByUsername(string $username): ?User
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        return $userRepository->findOneBy(['username' => $username]);
    }
}