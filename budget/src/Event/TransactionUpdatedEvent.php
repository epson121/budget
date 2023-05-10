<?php

namespace App\Event;

use App\Entity\Transaction;
use Symfony\Contracts\EventDispatcher\Event;

class TransactionUpdatedEvent extends Event
{
    public const NAME = 'transaction.updated';

    public function __construct(
        private Transaction $oldTransaction,
        private Transaction $newTransaction
    ) {
    }

    /**
     * @return Transaction
     */
    public function getOldTransaction(): Transaction
    {
        return $this->oldTransaction;
    }

    public function getNewTransaction(): Transaction
    {
        return $this->newTransaction;
    }

}