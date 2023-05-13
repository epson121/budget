<?php

namespace App\Test\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TransactionControllerTest extends WebTestCase {


    public function testGetAll() {

        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneBy(['username' => 'username-1']);

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        $client->request('GET', '/api/transactions');
        $response = $client->getResponse();
        
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertResponseIsSuccessful();
        $this->assertSame(200, $response->getStatusCode());


        $responseData = json_decode($response->getContent(), true);
        
        $this->assertCount(3, $responseData);
        $this->assertContains('Description 1', $responseData[0]);
        $this->assertContains('Description 2', $responseData[1]);
    }

    /**
     * @dataProvider provideTransactionGetData
     */
    public function testGet($id, $description, $statusCode, $amount, $type) {

        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneBy(['username' => 'username-1']);

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        $client->request('GET', '/api/transactions/' . $id);
        $response = $client->getResponse();
        
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertResponseIsSuccessful();
        $this->assertSame($statusCode, $response->getStatusCode());


        $responseData = json_decode($response->getContent(), true);
        
        $this->assertCount(7, $responseData);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertSame($id, $responseData['id']);
        $this->assertContains($description, $responseData);
        $this->assertArrayHasKey('amount', $responseData);
        $this->assertSame($amount, $responseData['amount']);
        $this->assertArrayHasKey('type', $responseData);
        $this->assertSame($type, $responseData['type']);

    }

    public function provideTransactionGetData()
    {
        return [
            [
                1,
                'Description 1',
                200,
                20,
                'expense'
            ],
            [
                3,
                'Description 3',
                200,
                10,
                'deposit'
            ]
        ];
    }

    public function testDeleteAllowed() {

        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneBy(['username' => 'username-1']);

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        $client->request('DELETE', '/api/transactions/2');
        $response = $client->getResponse();
        
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertResponseIsSuccessful();
        $this->assertSame(200, $response->getStatusCode());


        $responseData = json_decode($response->getContent(), true);
        
        $this->assertCount(1, $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertStringContainsString('Transaction deleted.', $responseData['message']);
    }

    public function testDeleteNotAllowed() {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneBy(['username' => 'username-1']);

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        $client->request('DELETE', '/api/transactions/10');
        $response = $client->getResponse();
        
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertSame(400, $response->getStatusCode());


        $responseData = json_decode($response->getContent(), true);
        
        $this->assertCount(1, $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertStringContainsString('Transaction with given ID does not exist.', $responseData['message']);
    }

    /**
     * @dataProvider provideTransactionPostData
     */
    public function testPost($postData, $statusCode, $expectedResponseMessage) {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneBy(['username' => 'username-1']);

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        $client->request(
            'POST',
            '/api/transactions',
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
     * @dataProvider provideTransactionPutData
     */
    public function testPut($transactionId, $putData, $statusCode, $expectedResponseMessage) {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneBy(['username' => 'username-1']);

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        $client->request(
            'PUT',
            '/api/transactions/' . $transactionId,
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

    public function provideTransactionPostData()
    {
        return [
            [
                [
                    "category" => 1,
                    "created_at" => "2024-04-23 18:25:22",
                    "amount" => "10",
                    "type" => "deposit",
                    "description" => "Some description"
                ],
                201,
                'Successfully created a transaction.'
            ],
            [
                [
                    "category" => 1,
                    "created_at" => "2024-04-23 18:25:22",
                    "amount" => "10",
                    "description" => "Some description"
                ],
                400,
                'Transaction type not properly set'
            ],
            [
                [
                    "category" => 1,
                    "created_at" => "2024-04-23 18:25:22",
                    "type" => "deposit",
                    "description" => "Some description"
                ],
                400,
                'Amount value should be positive number'
            ]
        ];
    }

    public function provideTransactionPutData()
    {
        return [
            [
                1,
                [
                    "amount" => "100",
                ],
                200,
                'Successfully updated a transaction.'
            ],
            [
                2,
                [
                    "category" => 10,
                    "description" => "Some new description"
                ],
                400,
                'Transaction with given ID does not exist.'
            ],
            [
                3,
                [
                    "type" => "depo",
                    "description" => "Some description"
                ],
                400,
                'Transaction type not properly set'
            ]
        ];
    }



}