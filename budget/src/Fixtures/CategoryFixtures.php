<?php

namespace App\Fixtures;

use App\Api\Data\FixturesInterface;

class CategoryFixtures implements FixturesInterface {

    public function getFixtures() : array
    {
        return [
            [
                'name' => 'Food'
            ],
            [
                'name' => 'Utilities'
            ],
            [
                'name' => 'Car'
            ],
            [
                'name' => 'Accomodations'
            ],
            [
                'name' => 'Travel'
            ],
            [
                'name' => 'Gifts'
            ],
        ];
    }
}