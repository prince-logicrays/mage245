<?php

namespace Logicrays\DispatchDate\Controller\Date;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Catalog\Model\ProductRepository;
use Logicrays\DispatchDate\Helper\Data;

class Index extends Action
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @param Context $context
     * @param DateTime $date
     * @param Registry $registry
     * @param ProductRepository $productRepository
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        DateTime $date,
        Registry $registry,
        ProductRepository $productRepository,
        Data $helper
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->date = $date;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        $this->helper = $helper;
    }

    /**
     * Updated order estimation date
     *
     * @return json
     */
    public function execute()
    {
        // $currentProductId = $this->context->getRequest()->getParam('product_id');
        $days = $this->context->getRequest()->getParam('days');
        // $currentProduct = $this->productRepository->getById($currentProductId);

        if ($days) {
            $updCateEstimationDate = $this->helper->getProductDispatchDate($days);
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData(["updateEstimationDate" => $updCateEstimationDate, "suceess" => true]);
        return $resultJson;
    }
}
