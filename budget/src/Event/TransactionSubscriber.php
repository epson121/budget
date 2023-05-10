<?php

namespace App\Event;

use App\Entity\Transaction;
use App\Event\TransactionCreatedEvent;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TransactionSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private UserRepository $userRepository
    ) {}

    public static function getSubscribedEvents()
    {
        return [
            TransactionCreatedEvent::NAME => 'onTransactionCreated',
            TransactionDeletedEvent::NAME => 'onTransactionDeleted'
        ];
    }

    public function onTransactionCreated(
        TransactionCreatedEvent $event
    ) {
        $transaction = $event->getTransaction();
        $user = $transaction->getUser();
        $balance = $user->getBalance();
        if ($transaction->getType() == Transaction::TYPE_DEPOSIT) {
            $balance += $transaction->getAmount();
        } else if ($transaction->getType() == Transaction::TYPE_EXPENSE) {
            $balance -= $transaction->getAmount();
        }

        $user->setBalance($balance);
        $this->userRepository->save($user, true);
    }

    public function onTransactionDeleted(
        TransactionDeletedEvent $event
    ) {
        $transaction = $event->getTransaction();
        $user = $transaction->getUser();
        $balance = $user->getBalance();
        if ($transaction->getType() == Transaction::TYPE_DEPOSIT) {
            $balance -= $transaction->getAmount();
        } else if ($transaction->getType() == Transaction::TYPE_EXPENSE) {
            $balance += $transaction->getAmount();
        }

        $user->setBalance($balance);
        $this->userRepository->save($user, true);
    }
}