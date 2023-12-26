<?php

use Magento\Framework\App\Bootstrap;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
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
		// ini_set('auto_detect_line_endings',TRUE);
		ini_set('display_errors', 1);

		// Create an instance of the Magento Object Manager
		$objectManager = $bootstrap->getObjectManager();

		// Use the Object Manager to get instances of necessary classes
		$attributeCollectionFactory = $objectManager->get(AttributeCollectionFactory::class);

		// Get the product attribute collection
		$attributeCollection = $attributeCollectionFactory->create()->addFieldToFilter('is_user_defined', 1);
		// Output the list of product attributes
		foreach ($attributeCollection as $attribute) {
			echo "<pre>";
			echo $attribute->getFrontendLabel() . "\n";
		}
	}
}
