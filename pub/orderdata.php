<?php

use Magento\Framework\App\Bootstrap;
require __DIR__ . '/../app/bootstrap.php';
ini_set('display_errors', 1);
ini_set('max_execution_time', "-1");
ini_set('memory_limit', "-1");
//ini_set('auto_detect_line_endings',TRUE);




$exporter = new ExportProducts();


class ExportProducts
{

	// Initialize the Mage application
	function __construct()
	{

		$bootstrap     = Bootstrap::create(BP, $_SERVER);
		$objectManager = $bootstrap->getObjectManager();

		$state = $objectManager->get('Magento\Framework\App\State');
		$state->setAreaCode('adminhtml');

		ini_set('max_execution_time', "-1");
		ini_set('memory_limit', "-1");
		//ini_set('auto_detect_line_endings',TRUE);
		ini_set('display_errors', 1);

		$orderId = 1;
		$order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($orderId);
		echo "<pre>";
		print_r($order->debug());exit;
	}
}