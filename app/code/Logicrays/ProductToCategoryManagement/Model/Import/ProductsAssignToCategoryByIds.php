<?php

namespace Logicrays\ProductToCategoryManagement\Model\Import;

use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\ImportExport\Helper\Data as ImportHelper;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class Courses
 */
class ProductsAssignToCategoryByIds extends AbstractEntity
{
    public const ENTITY_CODE = 'products_assign_to_category_by_ids';
    public const SKU_COLUMN = 'sku';

    /**
     * If we should check column names
     */
    protected $needColumnCheck = true;

    /**
     * Need to log in import history
     */
    protected $logInHistory = true;

    /**
     * Valid column names
     */
    protected $validColumnNames = [
        'sku',
        'category_ids'
    ];

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * Courses constructor.
     *
     * @param JsonHelper $jsonHelper
     * @param ImportHelper $importExportData
     * @param Data $importData
     * @param ResourceConnection $resource
     * @param Helper $resourceHelper
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param CollectionFactory $_productCollectionFactory
     * @param ProductRepositoryInterface $_productRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        JsonHelper $jsonHelper,
        ImportHelper $importExportData,
        Data $importData,
        ResourceConnection $resource,
        Helper $resourceHelper,
        ProcessingErrorAggregatorInterface $errorAggregator,
        CollectionFactory $_productCollectionFactory,
        ProductRepositoryInterface $_productRepository,
        LoggerInterface $logger
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->resource = $resource;
        $this->connection = $resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->errorAggregator = $errorAggregator;
        $this->_productCollectionFactory = $_productCollectionFactory;
        $this->_productRepository = $_productRepository;
        $this->_logger = $logger;
        $this->initMessageTemplates();
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return static::ENTITY_CODE;
    }

    /**
     * Get available columns
     *
     * @return array
     */
    public function getValidColumnNames(): array
    {
        return $this->validColumnNames;
    }

    /**
     * Row validation
     *
     * @param array $rowData
     * @param int $rowNum
     *
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum): bool
    {
        $name = $rowData['sku'] ?? '';
        $duration = $rowData['category_ids'] ?? '';

        if (!$name) {
            $this->addRowError('SKUIsRequired', $rowNum);
        }

        if (!$duration) {
            $this->addRowError('CategoryIdsIsRequired', $rowNum);
        }

        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }

        $this->_validatedRows[$rowNum] = true;

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * Init Error Messages
     */
    private function initMessageTemplates()
    {
        $this->addMessageTemplate(
            'SKUIsRequired',
            __('The SKU cannot be empty.')
        );
        $this->addMessageTemplate(
            'CatgoryIdsIsRequired',
            __('The Catgory Ids cannot be empty.')
        );
    }

    /**
     * Import data
     *
     * @return bool
     *
     * @throws Exception
     */
    protected function _importData(): bool
    {
        switch ($this->getBehavior()) {
            case Import::BEHAVIOR_DELETE:
                $this->saveAndReplaceEntity();
                break;
            case Import::BEHAVIOR_REPLACE:
                $this->saveAndReplaceEntity();
                break;
            case Import::BEHAVIOR_APPEND:
                $this->saveAndReplaceEntity();
                break;
        }

        return true;
    }

    /**
     * Save and replace entities
     *
     * @return void
     */
    private function saveAndReplaceEntity()
    {
        $behavior = $this->getBehavior();
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $productSkus = [];
            $categoryAssignments = [];

            foreach ($bunch as $rowNum => $row) {
                if (!$this->validateRow($row, $rowNum)) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);

                    continue;
                }

                $this->countItemsCreated += (int) !isset($row[static::SKU_COLUMN]);
                $this->countItemsUpdated += (int) isset($row[static::SKU_COLUMN]);

                $productSku = $row['sku'];
                $categoryIds = explode(',', $row['category_ids']);

                // Collect product SKUs and category assignments
                $productSkus[] = $productSku;
                $categoryAssignments[$productSku] = $categoryIds;
            }

            $this->saveProductToCategories($behavior, $productSkus, $categoryAssignments);
        }
    }

    /**
     * Save Product To Categories
     *
     * @param [type] $behavior
     * @param [type] $productSkus
     * @param [type] $categoryAssignments
     * @return void
     */
    protected function saveProductToCategories($behavior, $productSkus, $categoryAssignments)
    {
        if (empty($productSkus)) {
            return;
        }

        $isDeleteEvent = false;
        $products = $this->_productCollectionFactory->create()
            ->addAttributeToFilter('sku', ['in' => $productSkus]);

        if (Import::BEHAVIOR_DELETE === $behavior) {
            $isDeleteEvent = true;
        }

        if (Import::BEHAVIOR_APPEND === $behavior) {
            $isDeleteEvent = false;
        }

        foreach ($products as $product) {
            $sku = $product->getSku();
            $categoryIds = $categoryAssignments[$sku] ?? [];
            if ($isDeleteEvent) {
                $existingCategoryIds = $product->getCategoryIds();
                $trimCategoryIds = array_map('trim', $categoryIds);
                $newCategoryIds = array_diff($existingCategoryIds, $trimCategoryIds);
                $product->setCategoryIds(empty($newCategoryIds) ? '' : $newCategoryIds);
                try {
                    $this->_productRepository->save($product);
                } catch (\Exception $e) {
                    $this->_logger->info($e->getMessage());
                    continue;
                }
            } else {
                $product->setCategoryIds($categoryIds);
                $this->_productRepository->save($product);
            }
        }
    }
}
