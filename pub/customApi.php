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

		$curl = $objectManager->create('\Magento\Framework\HTTP\Client\Curl');
		$url = 'http://127.0.0.1/mage245/pub/rest/V1/custom/custom-api?value';
		$params = '';
		$curl->post($url,$params);
		$response = $curl->getBody();
		echo $response;
	}
}
