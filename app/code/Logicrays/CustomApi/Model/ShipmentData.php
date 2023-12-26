<?php

namespace Logicrays\CustomApi\Model;

use Magento\Framework\App\ResourceConnection;

class ShipmentData
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get Shipment Data
     *
     * @param [type] $orderId
     * @return void
     */
    public function getShipmentData($orderId)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('sales_shipment');
        $select = $connection->select()->from($tableName, ['driver_name'])->where('order_id = ?', $orderId);
        return $connection->fetchOne($select);
    }
}
