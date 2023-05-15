<?php
// tests/Service/NewsletterGeneratorTest.php
namespace App\Tests\Service;

use App\Service\CategoryService;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CategoryServiceTest extends KernelTestCase
{

    /**
     * @dataProvider provideCategoryExistsData
     */
    public function testCheckCategoryExists($categoryName, $expectedResult)
    {
        self::bootKernel();

        $container = static::getContainer();

        $userRepository = $container->get(UserRepository::class);

        $testUser = $userRepository->findOneBy(['username' => 'username-1']);

        $categoryService = $container->get(CategoryService::class);
        
        $exists = $categoryService->checkCategoryExists($categoryName, $testUser);

        $this->assertEquals($expectedResult, $exists);
    }

    public function provideCategoryExistsData()
    {
        return [
            [
                'category2',
                true
            ],
            [
                'category10',
                false
            ]
        ];
    }
    
}