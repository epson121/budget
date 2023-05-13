<?php

namespace App\Test\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase {

    public function testStatus() {

        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneBy(['username' => 'username-1']);

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        $client->request('GET', '/api/user/status');
        $response = $client->getResponse();
        
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertResponseIsSuccessful();
        $this->assertSame(200, $response->getStatusCode());


        $responseData = json_decode($response->getContent(), true);
        
        $this->assertCount(3, $responseData);
        
        $this->assertArrayHasKey('id', $responseData);
        $this->assertSame($testUser->getId(), $responseData['id']);
        
        $this->assertArrayHasKey('username', $responseData);
        $this->assertSame($testUser->getUsername(), $responseData['username']);

        $this->assertArrayHasKey('balance', $responseData);
    }

    public function testSummary() {

        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneBy(['username' => 'username-1']);

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        $client->request('GET', '/api/user/summary');
        $response = $client->getResponse();
        
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertResponseIsSuccessful();
        $this->assertSame(200, $response->getStatusCode());


        $responseData = json_decode($response->getContent(), true);

        $this->assertCount(2, $responseData);
        
        $this->assertArrayHasKey('tx_count', $responseData);
        $this->assertArrayHasKey('expense', $responseData['tx_count']);
        $this->assertArrayHasKey('deposit', $responseData['tx_count']);

        $this->assertArrayHasKey('tx_total', $responseData);
        $this->assertArrayHasKey('expense', $responseData['tx_total']);
        $this->assertArrayHasKey('deposit', $responseData['tx_total']);
    }
}