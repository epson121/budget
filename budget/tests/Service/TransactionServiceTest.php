<?php
// tests/Service/NewsletterGeneratorTest.php
namespace App\Tests\Service;

use App\Entity\Transaction;
use App\Service\CategoryService;
use App\Repository\UserRepository;
use App\Service\TransactionService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TransactionServiceTest extends KernelTestCase
{

    /**
     * @dataProvider provideTransactionData
     */
    public function testGetTransactionSummary($transactions, $expectedResult)
    {
        self::bootKernel();

        $container = static::getContainer();

        $transactionService = $container->get(TransactionService::class);
        
        $summary = $transactionService->getTransactionSummary($transactions);

        var_dump($summary);

        $this->assertArrayHasKey('tx_count', $summary);
        $this->assertArrayHasKey('expense', $summary['tx_count']);
        $this->assertEquals($expectedResult['tx_count']['expense'], $summary['tx_count']['expense']);
        $this->assertArrayHasKey('deposit', $summary['tx_count']);
        $this->assertEquals($expectedResult['tx_count']['deposit'], $summary['tx_count']['deposit']);

        $this->assertArrayHasKey('tx_total', $summary);
        $this->assertArrayHasKey('expense', $summary['tx_total']);
        $this->assertEquals($expectedResult['tx_total']['expense'], $summary['tx_total']['expense']);
        $this->assertArrayHasKey('deposit', $summary['tx_total']);
        $this->assertEquals($expectedResult['tx_total']['deposit'], $summary['tx_total']['deposit']);

    }

    public function provideTransactionData()
    {
        return [
            [
                [
                    $this->getTransaction('expense', 20),
                    $this->getTransaction('deposit', 20)
                ],
                [
                    'tx_count' => [
                        'expense' => 1,
                        'deposit' => 1
                    ],
                    'tx_total' => [
                        'expense' => 20,
                        'deposit' => 20
                    ]
                ]
                    ],
                    [
                        [
                            $this->getTransaction('expense', 10),
                            $this->getTransaction('expense', 20),
                            $this->getTransaction('expense', 30),
                            $this->getTransaction('deposit', 10),
                            $this->getTransaction('deposit', 20)
                        ],
                        [
                            'tx_count' => [
                                'expense' => 3,
                                'deposit' => 2
                            ],
                            'tx_total' => [
                                'expense' => 60,
                                'deposit' => 30
                            ]
                        ]
                    ]
        ];
    }

    private function getTransaction($type, $amount) {
        $tx = new Transaction();
        $tx->setAmount($amount);
        $tx->setType($type);

        return $tx;
    }
    
}