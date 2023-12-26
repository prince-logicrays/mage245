<?php

namespace Logicrays\DispatchDate\Block;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\AbstractProduct;
use Logicrays\DispatchDate\Helper\Data;

class DispatchDate extends AbstractProduct
{
    /**
     * @param Context $context
     * @param Registry $registry
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helper,

        array $data
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    public function getDispatchDate()
    {
        $getProductDispatchDate = $this->helper->getProductDispatchDate();
        return $getProductDispatchDate;
    }
}
