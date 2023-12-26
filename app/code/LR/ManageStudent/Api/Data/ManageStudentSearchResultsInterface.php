<?php
declare(strict_types=1);

namespace LR\ManageStudent\Api\Data;

interface ManageStudentSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Email list.
     * @return \LR\StudentForm\Api\Data\ManageStudentInterface[]
     */
    public function getItems();

    /**
     * Set Email list.
     * @param \LR\StudentForm\Api\Data\ManageStudentInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $count
     * @return $this
     */
    public function setTotalCount($count);
}

