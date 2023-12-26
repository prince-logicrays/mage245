<?php

namespace Logicrays\IncreaseQty\Plugin\Product\View\Type;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;

class ConfigurablePlugin
{
    /**
     * JsonEncoder variable
     *
     * @var jsonEncoder
     */
    protected $jsonEncoder;

    /**
     * JsonDecoder variable
     *
     * @var jsonDecoder
     */
    protected $jsonDecoder;

    /**
     * StockRegistry variable
     *
     * @var [type]
     */
    protected $stockRegistry;

    /**
     * __construct function
     *
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param StockRegistryInterface $stockRegistry
     * @param StoreManagerInterface $storeManager
     * @param StockConfigurationInterface $stockConfiguration
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        StockRegistryInterface $stockRegistry,
        StoreManagerInterface $storeManager,
        StockConfigurationInterface $stockConfiguration,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
        $this->stockRegistry = $stockRegistry;
        $this->storeManager = $storeManager;
        $this->stockConfiguration = $stockConfiguration;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * AfterGetJsonConfig function
     *
     * @param Configurable $subject
     * @param mixed $result
     * @return array
     */
    public function afterGetJsonConfig(Configurable $subject, $result)
    {
        $result = $this->jsonDecoder->decode($result);
        foreach ($subject->getAllowProducts() as $product) {

            $stockitem = $this->stockRegistry->getStockItem(
                $product->getId(),
                $product->getStore()->getWebsiteId()
            );

            $isStockManage = $this->isStockManage();
            if ($stockitem['use_config_manage_stock']) {
                if ($isStockManage) {
                    $adminStockManageQty = $stockitem->getQty();
                } else {
                    $adminStockManageQty = $stockitem->getQty().'_0';
                }
            } elseif ($stockitem['manage_stock']) {
                $adminStockManageQty = $stockitem->getQty();
            } else {
                $adminStockManageQty = $stockitem->getQty().'_0';
            }
            $quantities[$product->getId()] = $adminStockManageQty;
            $result['quantities'][$product->getId()] = $quantities;
        }
        return $this->jsonEncoder->encode($result);
    }

    /**
     * IsStockManage function
     *
     * @return boolean
     */
    public function isStockManage()
    {
        $storeId = $this->storeManager->getStore()->getStoreId();
        $isManageStockGlobal = $this->stockConfiguration->getManageStock();
        $isManageStockStore = $this->scopeConfig->getValue(
            'cataloginventory/item_options/manage_stock',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $isManageStockGlobal || $isManageStockStore;
    }
}
