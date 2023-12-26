<?php
namespace LR\CsvExport\Plugin;

class AccesorySku
{
    public function aroundExport(
        \Magento\CatalogImportExport\Model\Export\Product $subject,
        \Closure $proceed,
        $filter = null,
        $store = null
    ) {
        // Call the original method to get the product data
        $result = $proceed($filter, $store);

        // Add your custom field to the header
        $result[0][] = 'accessory_skus';

        // Loop through each product and add the custom field value
        foreach ($result as &$row) {
            // Get the product ID from the row
            $productId = $row['entity_id'];

            // Replace this with your logic to fetch the custom field value based on the product ID
            $customFieldValue = $this->getCustomFieldValue($productId);

            // Add the custom field value to the row
            $row[] = $customFieldValue;
        }

        return $result;
    }

    // Replace this with your logic to fetch the custom field value based on the product ID
    private function getCustomFieldValue($productId)
    {
        // Your logic here to fetch the custom field value based on the product ID
        return 'Custom Value for Product ID ' . $productId;
    }
}
