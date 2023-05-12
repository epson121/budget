<?php

namespace App\Validators;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validation;

class CategoryValidator {

    /**
     * @param $data
     * @return ConstraintViolationInterface|null
     */
    public function validateCategoryData($data): ?ConstraintViolationInterface
    {
        $validator = Validation::createValidator();

        $lengthConstraint = new Length(['max' => 50]);
        $lengthConstraint->maxMessage = 'Name value should have at most {{ limit }} characters.';

        $alphanumConstraint = new Regex("/[a-zA-z0-9\s]*/", "Name should be an alphanumeric value");

        $categoryDataConstraint = new Collection([
            'name' => [$alphanumConstraint, $lengthConstraint]
        ]);

        $error = $validator->validate(
            [
                'name' => $data['name']
            ],
            $categoryDataConstraint
        );

        return count($error) ? $error->get(0) : null;
    }
}