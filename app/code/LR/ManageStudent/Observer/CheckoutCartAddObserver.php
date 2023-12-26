<?php

namespace LR\ManageStudent\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use LR\ManageStudent\Model\ResourceModel\ManageStudent\CollectionFactory as ManageStudentCollectionFactory;

/**
 * CheckoutCartAddObserver
 */
class CheckoutCartAddObserver implements ObserverInterface
{
    protected $_layout;
    protected $_storeManager;
    protected $_request;
    protected $managestudentCollectionFactory;
    
    /**
     * __construct
     *
     * @param \Magento\Store\Model\StoreManagerInterface storeManager
     * @param \Magento\Framework\View\LayoutInterface layout
     * @param \Magento\Framework\App\RequestInterface request
     * @param \Magento\Framework\Serialize\SerializerInterface serializer
     *
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\App\RequestInterface $request,
        ManageStudentCollectionFactory $managestudentCollectionFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->_layout = $layout;
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        $this->managestudentCollectionFactory = $managestudentCollectionFactory;
        $this->serializer = $serializer;
    }
    
    /**
     * execute
     *
     * @param EventObserver observer
     *
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $productParamsData = $this->getStudents();

        if (isset($productParamsData)) {
            $item = $observer->getQuoteItem();

            $additionalOptions = [];
            if (is_array($productParamsData)) {
                foreach ($productParamsData as $str) {
            $additionalOptions[] = [
                            'label' => 'Student',
                            'value' => $str,
                    ];
                }
            }

            if (count($additionalOptions) > 0) {
                $item->addOption([
                    'product_id' => $item->getProductId(),
                    'code' => 'additional_options',
                    'value' => $this->serializer->serialize($additionalOptions),
                ]);
            }
        }
    }

    public function getStudents()
    {
        $studentsId = $this->_request->getParam('students');
        $collection = $this->managestudentCollectionFactory->create();
        $collection->addFieldToFilter("id", $studentsId);
        $studentData = array();
        foreach ($collection as $student) {
            $studentData[] = $student->getFirstName() . " " . $student->getLastName();
        }
        return $studentData;
    }
}