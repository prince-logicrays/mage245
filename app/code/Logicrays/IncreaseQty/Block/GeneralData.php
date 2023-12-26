<?php
namespace Logicrays\IncreaseQty\Block;

class GeneralData extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * __construct function
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        array $data = []
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->stockRegistry = $stockRegistry;
        parent::__construct($context, $data);
    }

    /**
     * GetStockItem function
     *
     * @param int $itemId
     * @return array
     */
    public function getStockItem($itemId)
    {
        $stockItem = $this->stockRegistry->getStockItem($itemId);
        return $stockItem;
    }

    /**
     * IsStockManage function
     *
     * @return boolean
     */
    public function isStockManage()
    {
        $storeId = $this->storeManager->getStore()->getStoreId();

        // Check global "Manage Stock" configuration first
        $isManageStockGlobal = $this->stockConfiguration->getManageStock();

        // Check store-specific configuration
        $isManageStockStore = $this->scopeConfig->getValue(
            'cataloginventory/item_options/manage_stock',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        // If Manage Stock is enabled either globally or for the specific store, return true
        return $isManageStockGlobal || $isManageStockStore;
    }
}
