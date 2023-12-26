<?php

namespace Logicrays\CustomApi\Model\Api;

use Psr\Log\LoggerInterface;

class Custom
{
    protected $logger;

    public function __construct(
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function getPost($value)
    {
        $response = ['success' => false];
        try {
            // Your Code here
            $response = [
                'entity' => [
                    'base_currency_code' => 'USD',
                    'base_discount_amount' => -4.5,
                    'base_grand_total' => 45.5
                ]
            ];
            // $response = ['success' => true, 'message' => $value];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => $e->getMessage()];
            $this->logger->info($e->getMessage());
        }
        $returnArray = json_encode($response);
        return $returnArray;
    }
}
