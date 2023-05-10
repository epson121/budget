<?php

namespace App\Event;

use App\Entity\Category;
use App\Fixtures\CategoryFixtures;
use App\Repository\CategoryRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private CategoryRepository $categoryRepository,
        private CategoryFixtures $categoryFixtures
    ) {}

    public static function getSubscribedEvents()
    {
        return [
            UserCreatedEvent::NAME => 'onUserCreated',
        ];
    }

    public function onUserCreated(
        UserCreatedEvent $event
    ) {
        $user = $event->getUser();
        $categoryFixtures = $this->categoryFixtures->getFixtures();

        foreach($categoryFixtures as $fixture) {
            $category = new Category();
            $category->setName($fixture['name']);
            $category->setUser($user);

            $this->categoryRepository->save($category, true);
        }
    }
}