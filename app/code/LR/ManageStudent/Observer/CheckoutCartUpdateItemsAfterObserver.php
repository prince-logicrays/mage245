<?php

namespace LR\ManageStudent\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Checkout\Model\Cart\Interceptor;

class CheckoutCartUpdateItemsAfterObserver implements ObserverInterface
{

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $cart = $observer->getData('cart');
        $quote = $cart->getData('quote');
        $items = $quote->getAllVisibleItems();

        foreach($items as $item)
        {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/145.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info(print_r($item->debug(), true));

            if($item->getSku()=='MSH09-32-Black'){
                $item->setCustomPrice(8);
                $item->setOriginalCustomPrice(8);
                $item->getProduct()->setIsSuperMode(true);
                $item->save();
            }

        }
        $quote->save();
    }
}
