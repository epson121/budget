<?php

namespace App\Validators;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Validation;

class UserValidator {

    /**
     * @param $data
     * @return ConstraintViolationInterface|null
     */
    public function validateRegistrationData($data): ?ConstraintViolationInterface
    {
        $validator = Validation::createValidator();

        $usernameConstraint = new Length(['min' => 6]);
        $usernameConstraint->minMessage = 'Username value should have at least {{ limit }} characters.';

        $passwordConstraint = new Length(['min' => 6]);
        $passwordConstraint->minMessage = 'Password value should have at least {{ limit }} characters.';

        $registrationDataConstraint = new Collection([
            'username' => $usernameConstraint,
            'password' => $passwordConstraint,
            'confirm_password' => new EqualTo(['value' => $data['password'], 'message' => 'Passwords do not match'])
        ]);

        $error = $validator->validate(
            [
                'username' => $data['username'],
                'confirm_password' => $data['confirm_password'],
                'password' => $data['password']
            ],
            $registrationDataConstraint
        );


        return count($error) ? $error->get(0) : null;
    }

}