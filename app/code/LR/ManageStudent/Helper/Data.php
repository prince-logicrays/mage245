<?php
namespace LR\ManageStudent\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const ENABLED = 'managestudent/general/enabled';
    // const FRONTEND_ENABLED = 'managestudent/general/frontend_enabled';

    protected $serializer;
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }
    
    public function isEnabled($storeId = null)
    {
        return $this->getConfigData(self::ENABLED, $storeId);
    }

    // public function isFrontendEnabled($storeId = null)
    // {
    //     return $this->getConfigData(self::FRONTEND_ENABLED, $storeId);
    // }

    public function getConfigData($configPath, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}