<?php

namespace App\Validators;

use App\Entity\Transaction;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validation;

class TransactionValidator {

    /**
     * @param $data
     * @return ConstraintViolationInterface|null
     */
    public function validateTransactionData($data): ?ConstraintViolationInterface
    {
        
        $validator = Validation::createValidator();

        $positiveNumberConstraint = new Positive();
        $positiveNumberConstraint->message = 'Amount value should be positive number';

        $typeConstraint = new Choice([Transaction::TYPE_EXPENSE, Transaction::TYPE_DEPOSIT]);
        $typeConstraint->message = "Transaction type not properly set";

        $dateConstraint = new DateTime();
        $dateConstraint->message = "Date must follow the Y-m-d H:i:s format.";

        $transactionDataConstraint = new Collection([
            'amount' => [$positiveNumberConstraint],
            'type' => $typeConstraint,
            'created_at' => $dateConstraint
        ]);

        $error = $validator->validate(
            [
                'amount' => $data['amount'],
                'type' => $data['type'],
                'created_at' => $data['created_at']
            ],
            $transactionDataConstraint
        );

        return count($error) ? $error->get(0) : null;
    }
}