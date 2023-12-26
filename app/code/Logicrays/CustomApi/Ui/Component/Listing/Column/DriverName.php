<?php

namespace Logicrays\CustomApi\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Logicrays\CustomApi\Model\ShipmentData;

class DriverName extends Column
{
    /**
     * @var ShipmentData
     */
    protected $shipmentData;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param ShipmentData $shipmentData
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        ShipmentData $shipmentData,
        array $components = [],
        array $data = []
    ) {
        $this->shipmentData = $shipmentData;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source For Driver Name
     *
     * @param array $dataSource
     * @return void
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $orderId = $item['entity_id']; // Assuming entity_id is the order ID
                $item['driver_name'] = $this->shipmentData->getShipmentData($orderId);
            }
        }
        return $dataSource;
    }
}
