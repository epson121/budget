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
            TransactionDeletedEvent::NAME => 'onTransactionDeleted',
            TransactionUpdatedEvent::NAME => 'onTransactionUpdated'
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

    public function onTransactionUpdated(
        TransactionUpdatedEvent $event
    ) {
        $oldTransaction = $event->getOldTransaction();
        $newTransaction = $event->getNewTransaction();
        $user = $newTransaction->getUser();
        $balance = $user->getBalance();

        $oldAmount = $oldTransaction->getAmount();
        $newAmount = $newTransaction->getAmount();

        if ($oldTransaction->getType() != $newTransaction->getType()) {
            
            if ($newTransaction->getType() == Transaction::TYPE_DEPOSIT) {
                $balance += $oldAmount;
                $balance += $newAmount;
            } else {
                $balance -= $oldAmount;
                $balance -= $newAmount;
            }
        } else {
            if ($newTransaction->getType() == Transaction::TYPE_DEPOSIT) {
                $balance -= $oldAmount;
                $balance += $newAmount;
            } else {
                $balance += $oldAmount;
                $balance -= $newAmount;
            }
        }

        $user->setBalance($balance);
        $this->userRepository->save($user, true);
    }
}