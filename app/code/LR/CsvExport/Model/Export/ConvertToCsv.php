<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace LR\CsvExport\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\MetadataProvider;
use Magento\Ui\Model\Export\ConvertToCsv as VendorConvertToCsv;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem as FilesystemDirectoryList;

class ConvertToCsv extends VendorConvertToCsv
{
    /**
     * @var DirectoryList
     */
    protected $directory;

    /**
     * @var MetadataProvider
     */
    protected $metadataProvider;

    /**
     * @var int|null
     */
    protected $pageSize = null;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @param Filesystem $filesystem
     * @param Filter $filter
     * @param MetadataProvider $metadataProvider
     * @param File $file
     * @param Csv $csv
     * @param integer $pageSize
     */
    public function __construct(
        Filesystem $filesystem,
        Filter $filter,
        MetadataProvider $metadataProvider,
        File $file,
        Csv $csv,
        FilesystemDirectoryList $filesystemDirectoryList,
        $pageSize = 200
    ) {
        $this->filter = $filter;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->metadataProvider = $metadataProvider;
        $this->file = $file;
        $this->csv = $csv;
        $this->filesystemDirectoryList = $filesystemDirectoryList;
        $this->pageSize = $pageSize;
    }

    /**
     * Returns CSV file
     *
     * @return array
     * @throws LocalizedException
     */
    public function getCsvFile()
    {
        $mediaPath = $this->filesystemDirectoryList->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        $csvPath = $mediaPath."mycsv.csv";
        if ($this->file->isExists($csvPath)) {
            $this->csv->setDelimiter(",");
            $csvRead = $this->csv->getData($csvPath);
            $tableColumns = ['Customer Group'];
            foreach ($csvRead as $key => $value) {
                if ($key === 0) {
                    $header = $value;
                } else {
                    if (array_intersect($tableColumns, $header)) {
                        $csvGlobal[$value[0]] = array_combine($header, $value);
                    }
                }
            }
        }
        $component = $this->filter->getComponent();
        // md5() here is not for cryptographic use.
        // phpcs:ignore Magento2.Security.InsecureFunction
        $name = md5(microtime());
        $file = 'export/'. $component->getName() . $name . '.csv';

        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $dataProvider = $component->getContext()->getDataProvider();
        $fields = $this->metadataProvider->getFields($component);
        $options = $this->metadataProvider->getOptions();

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->writeCsv($this->metadataProvider->getHeaders($component));

        $i = 1;
        $searchCriteria = $dataProvider->getSearchCriteria()
            ->setCurrentPage($i)
            ->setPageSize($this->pageSize);
        $totalCount = (int) $dataProvider->getSearchResult()->getTotalCount();
        while ($totalCount > 0) {
            $items = $dataProvider->getSearchResult()->getItems();
            $array = array_column($items, 'customer_email');
            foreach ($items as $item) {
                if ($component->getName() == 'sales_order_grid') {
                    if ($item['customer_group'] == '' && $item['entity_id']) {
                        $item['customer_group'] = $csvGlobal[$item['entity_id']]['Customer Group'];
                    }
                }
                $this->metadataProvider->convertDate($item, $component->getName());
                $stream->writeCsv($this->metadataProvider->getRowData($item, $fields, $options));
            }
            $searchCriteria->setCurrentPage(++$i);
            $totalCount = $totalCount - $this->pageSize;
        }
        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true
        ];
    }
}
