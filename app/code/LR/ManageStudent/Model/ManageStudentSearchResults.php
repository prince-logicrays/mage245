<?php

declare(strict_types=1);

namespace LR\ManageStudent\Model;

use LR\ManageStudent\Api\Data\ManageStudentSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class ManageStudentSearchResults extends SearchResults implements ManageStudentSearchResultsInterface
{

}
