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
				
		$x = (isset($_GET['x'])) ? (int)$_GET['x'] : 1;
		$pageSize = 100;
		
		$path = '/var/www/html/mage245/pub/media';
		$name = 'category-id-17';
		$file = $path . '/' . $name . '.csv';
		$categoryIds = [17];
		$products = $objectManager->create('\Magento\Catalog\Model\Product')->getCollection()
					->addAttributeToSelect('*')
					->addCategoriesFilter(['in' => $categoryIds])
					->setCurPage($x);
		
		$products->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
		$products->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);

		$count = $products->getSize();
		if($x == 1){
			$fp = fopen($file, 'w');
			$header = array(
				'id',
				'sku',
				'name'
			);
			fputcsv($fp, $header);
		}
		//if(false){
		if(count($products) && $x <= ceil($count / $pageSize)){
        	$fp = fopen($file, 'a');
			foreach ($products as $i => $product) {

				$productData = [];
				$productData['id'] = $product->getId();
				$productData['sku'] = $product->getSku();
				$productData['name'] = $product->getName();
				fputcsv($fp, $productData);
			}
			$progress = ($x*100) / ceil($count / $pageSize);
			echo '<div>'.((($x*$pageSize) > $count) ? 'Exported ' : 'Exporting ').round($progress,2).'%</div>';
			echo '<div style="height:20px; background: #111; width: '.$progress.'%"></div><br>';
			echo ((($x*$pageSize) > $count) ? $count : ($x*$pageSize)) .' of '.$count.' Products Exported';

			$x++;
			print '<meta http-equiv="Refresh" content="0; url='.$_SERVER['PHP_SELF'].'?x='.$x.'">';
			exit;
		}else{
			$filetype = filetype($file);
	       	$filename = basename($file);
	       	header ("Content-Type: ".$filetype);
	       	header ("Content-Length: ".filesize($file));
	       	header ("Content-Disposition: attachment; filename=".$filename);
	       	readfile($file);
			//unlink($file);
			
		}
	}
}