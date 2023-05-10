<?php

namespace App\Event;

use App\Entity\Transaction;
use Symfony\Contracts\EventDispatcher\Event;

class TransactionCreatedEvent extends Event
{
    public const NAME = 'transaction.created';

    public function __construct(
        private Transaction $transaction
    ) {
    }

    /**
     * @return Transaction
     */
    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }
}