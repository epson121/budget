<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Category;
use App\Entity\Transaction;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{

    public function load(ObjectManager $manager): void
    {
        $categoryNames = ['category1', 'category2', 'category3', 'category4'];

        $user1 = new User();
        $user1->setUsername('username-1');
        $user1->setPassword('password-1');
        $user1->setBalance(200);
        $manager->persist($user1);

        $user2 = new User();
        $user2->setUsername('username-2');
        $user2->setPassword('password-2');
        $user2->setBalance(200);
        $manager->persist($user2);

        $categories = [];

        foreach([$user1, $user2] as $user) {
            foreach ($categoryNames as $cat) {
                $category = new Category();
                $category->setName($cat);
                $category->setUser($user);
                $manager->persist($category);
                $categories[] = $category;
            }
        }

        $transaction1 = new Transaction();
        $transaction1->setAmount(20);
        $transaction1->setCategory($categories[0]);
        $transaction1->setDescription('Description 1');
        $transaction1->setType('expense');
        $transaction1->setCreatedAt(new DateTimeImmutable('2023-05-11 08:00:00'));
        $transaction1->setUser($user1);
        $manager->persist($transaction1);

        $transaction2 = new Transaction();
        $transaction2->setAmount(30);
        $transaction2->setCategory($categories[1]);
        $transaction2->setDescription('Description 2');
        $transaction2->setType('expense');
        $transaction2->setCreatedAt(new DateTimeImmutable('2023-05-12 08:00:00'));
        $transaction2->setUser($user1);
        $manager->persist($transaction2);

        $transaction3 = new Transaction();
        $transaction3->setAmount(10);
        $transaction3->setCategory($categories[1]);
        $transaction3->setDescription('Description 3');
        $transaction3->setType('deposit');
        $transaction3->setCreatedAt(new DateTimeImmutable('2023-05-13 08:00:00'));
        $transaction3->setUser($user1);
        $manager->persist($transaction3);

        $manager->flush();
    }
}
