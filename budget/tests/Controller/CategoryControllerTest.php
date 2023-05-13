<?php

namespace App\Test\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryControllerTest extends WebTestCase {

    public function testGetAll() {

        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneBy(['username' => 'username-1']);

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        $client->request('GET', '/api/categories');
        $response = $client->getResponse();
        
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertResponseIsSuccessful();
        $this->assertSame(200, $response->getStatusCode());


        $responseData = json_decode($response->getContent(), true);
        
        $this->assertCount(4, $responseData);
        $this->assertContains('category1', $responseData[0]);
        $this->assertContains('category2', $responseData[1]);

    }

    public function testGet() {

        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneBy(['username' => 'username-1']);

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        $client->request('GET', '/api/categories/1');
        $response = $client->getResponse();
        
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertResponseIsSuccessful();
        $this->assertSame(200, $response->getStatusCode());


        $responseData = json_decode($response->getContent(), true);
        
        $this->assertCount(2, $responseData);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertSame(1, $responseData['id']);
        $this->assertContains('category1', $responseData);

    }

    public function testDeleteAllowed() {

        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneBy(['username' => 'username-1']);

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        $client->request('DELETE', '/api/categories/3');
        $response = $client->getResponse();
        
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertResponseIsSuccessful();
        $this->assertSame(200, $response->getStatusCode());


        $responseData = json_decode($response->getContent(), true);
        
        $this->assertCount(1, $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertStringContainsString('Category deleted.', $responseData['message']);
    }

    public function testDeleteNotAllowed() {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneBy(['username' => 'username-1']);

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        $client->request('DELETE', '/api/categories/1');
        $response = $client->getResponse();
        
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertSame(400, $response->getStatusCode());


        $responseData = json_decode($response->getContent(), true);
        
        $this->assertCount(1, $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertStringContainsString('Category has transactions, and can not be removed.', $responseData['message']);
    }

    /**
     * @dataProvider provideCategoryPostData
     */
    public function testPost($categoryName, $statusCode, $expectedResponseMessage) {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneBy(['username' => 'username-1']);

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        $postData = [
            'name' => $categoryName
        ];

        $client->request(
            'POST',
            '/api/categories',
            $postData,
            [],
            ['content_type' => 'application/json']
        );

        $response = $client->getResponse();
        
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertSame($statusCode, $response->getStatusCode());

        $responseString = $response->getContent();
        $this->assertStringContainsString($expectedResponseMessage, $responseString);
    }

    /**
     * @dataProvider provideCategoryPutData
     */
    public function testPut($categoryId, $categoryData, $statusCode, $expectedResponseMessage) {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneBy(['username' => 'username-1']);

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        $putData = $categoryData;

        $client->request(
            'PUT',
            '/api/categories/' . $categoryId,
            $putData,
            [],
            ['content_type' => 'application/json']
        );

        $response = $client->getResponse();
        
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertSame($statusCode, $response->getStatusCode());

        $responseString = $response->getContent();
        $this->assertStringContainsString($expectedResponseMessage, $responseString);
    }


    public function provideCategoryPostData()
    {
        return [
            [
                'category5',
                201,
                'Successfully created category.'
            ],
            [
                'category1',
                400,
                'This category already exist'
            ],
            [
                '??categ??',
                400,
                'Name should be an alphanumeric value'
            ],
        ];
    }

    public function provideCategoryPutData()
    {
        return [
            [
                1,
                [
                    'name' => 'New Name'
                ],
                200,
                'Successfully updated category'
            ],
            [
                5,
                [
                    'name' => 'New New Name'
                ],
                400,
                'Category with given ID does not exist.'
            ],
            [
                1,
                [
                    'name' => '??New Name'
                ],
                400,
                'Name should be an alphanumeric value'
            ],
        ];
    }



}