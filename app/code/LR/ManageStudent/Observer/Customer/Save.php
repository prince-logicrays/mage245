<?php

namespace LR\ManageStudent\Observer\Customer;

use LR\ManageStudent\Helper\Data as Helper;
use LR\ManageStudent\Model\ManageStudentFactory as ModelFactory;
use LR\ManageStudent\Model\ResourceModel\ManageStudent\CollectionFactory as CollectionFactory;
use LR\ManageStudent\Api\ManageStudentRepositoryInterface as ModelRepository;

class Save implements \Magento\Framework\Event\ObserverInterface
{
    protected $_request;

    protected $_helper;
    protected $_repository;
    protected $_modelFactory;
    protected $_collectionFactory;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        Helper $helper,
        ModelRepository $repository,
        ModelFactory $modelFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->_request = $request;

        $this->_helper = $helper;
        $this->_repository = $repository;
        $this->_modelFactory = $modelFactory;
        $this->_collectionFactory = $collectionFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getCustomer();
        $storeId = $customer->getStoreId();

        if(!$this->_helper->isEnabled($storeId)) {
            return $this;
        }

        $data = $this->_request->getParams();

        $collection = $this->_collectionFactory->create();
        $collection->addFieldToFilter('customer_id', array('eq' => $customer->getId()));
        $currentStudents = array();
        if($collection->count()) {
            foreach($collection as $model) {
                $currentStudents[$model->getId()] = $model;
            }
        }
        /**
         * Save manage students
         * If manage students tab is active
         * remove ['managestudent']['container_managestudent]
         * from $data['managestudent']['container_managestudent']['managestudent']
         */
        if($data && isset($data['managestudent']['container_managestudent']['managestudent'])) {
            $studentData = $data['managestudent']['container_managestudent']['managestudent'];
            if(array_key_exists('managestudent', $studentData)){
                $additionalemailsEmails = $studentData['managestudent'];
                foreach($additionalemailsEmails as $email) {
                    if(isset($email['id'])) {
                        $model = $currentStudents[$email['id']];
                        unset($currentStudents[$email['id']]);
                    } else {
                        $model = $this->_modelFactory->create();
                        $model->setCustomerId($customer->getId());
                    }

                    // if (!\Zend_Validate::is(trim($email['email']), 'EmailAddress')) {
                    //     throw new \Exception(__("student email address is not valid: %s", $email['email']));
                    // }
                    // if (!\Zend_Validate::is(trim($email['first_name']), 'NotEmpty')) {
                    //     throw new \Exception(__("Student name cannot be empty."));
                    // }

                    // if (!\Zend_Validate::is(trim($email['last_name']), 'NotEmpty')) {
                    //     throw new \Exception(__("Student name cannot be empty."));
                    // }
                    if(!$email['status']) {
                        $email['status'] = 0;
                    }

                    $model->setEmail(trim($email['email']));
                    $model->setFirstName(trim($email['first_name']));
                    $model->setLastName(trim($email['last_name']));
                    $model->setGrade(trim($email['grade']));
                    $model->setStatus($email['status']);
                    $this->_repository->save($model);
                }
            }
        }

        if($currentStudents && sizeof($currentStudents)) {
            foreach($currentStudents as $model) {
                $this->_repository->delete($model);
            }
        }

        return $this;
    }
}