<?php

namespace LR\ManageStudent\Controller\Student;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class NewAction extends \LR\ManageStudent\Controller\ManageStudent implements HttpGetActionInterface
{
    /**
     * @return \Magento\Framework\Controller\Result\Forward
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('form');
    }
}
