<?php

namespace Logicrays\DispatchDate\Helper;

use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTime as DefaultDateTime;
use Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory;

class Data extends AbstractHelper
{
    public const GET_HOLIDAY_VALUE = 'orderdelivery/order/holiday';

    public function __construct(
        Registry $registry,
        ScopeConfigInterface $scopeConfig,
        TimezoneInterface $timezone,
        DefaultDateTime $date
    ) {
        $this->registry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->timezone = $timezone;
        $this->date = $date;
    }

    /**
     * Get holiday value fron system config
     *
     * @return int
     */
    public function getHoliday()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::GET_HOLIDAY_VALUE, $storeScope);
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getProductDispatchDate($extraWorkingDays = null)
    {
        if ($extraWorkingDays) {
            $extraDays = $extraWorkingDays;
        } else {
            $currentProduct = $this->registry->registry('current_product');
            $dispatchDate = $currentProduct->getAttributeText('dispatch_date');
            if ($dispatchDate) {
                if (is_array($dispatchDate)) {
                    $data = [];
                    foreach ($dispatchDate as $value) {
                        if ($value) {
                            $data[] = $value;
                        }
                    }
                    $data = implode(" ", $data);
                    preg_match_all('/\d+/', $data, $matches);
                    $integerValues = $matches[0];
                    $extraDays = max($integerValues);
                } else {
                    preg_match_all('/\d+/', $dispatchDate, $matches);
                    $integerValues = $matches[0];
                    $extraDays = max($integerValues);
                }
            }
        }
        $currentDate = $this->timezone->date();
        $startDate = date("Y-m-d");

        /**
        * If current time hours is greater than or equals to 03:00 PM
        * It will add 1 day plus
        */
        if (date('H') >= 9) {
            $startDate = date("Y-m-d", time() + 86400);
        }
        $numberOfDaysToSkip = $extraDays; // Number of working days to skip

        $holidaysData = $this->getHoliday();
        $holidays = explode(',', $holidaysData);

        $startDate =  $this->getStartDate($startDate, $holidays);

        $numberofWorkingDays = $numberOfDaysToSkip;

            // Create DateTime object
        $dateTimeObject = $this->timezone->date(new \DateTime($startDate));

        $startDateTimeStamp = $this->date->gmtTimestamp($dateTimeObject);

        for ($i=1; $i<$numberofWorkingDays; $i++) {

            /**
             * Add 1 day to timestamp
            */
            $addDay = 86400;

            /**
             * Get what day it is next day
            * w - A numeric representation of the day (0 for Sunday, 6 for Saturday)
            */
            $nextDay = date('w', ($startDateTimeStamp+$addDay));

            /**
             * If it's holidays get $i-1
            */
            if (in_array($nextDay, $holidays)) {
                $i--;
            }

            // modify timestamp, add 1 day
            $startDateTimeStamp = $startDateTimeStamp+$addDay;
        }

        // Set TimeStamp
        $setTimeStampToFinalDate = $this->date->timestamp($startDateTimeStamp);
        // Define final date
        $finalDeliveryEstimationDate = null;
        $finalDeliveryEstimationDate = $this->date->date('l, d F Y', $setTimeStampToFinalDate);

        // Get date with working days excluding weekend
        $nextWorkingDay = $this->getNextWorkingDay($currentDate, $numberOfDaysToSkip);

        return $finalDeliveryEstimationDate;
    }

    /**
     * Get date with working days excluding weekend
     *
     * @param [type] $currentDate
     * @param integer $numberOfDaysToSkip
     * @return void
     */
    public function getNextWorkingDay($currentDate, $numberOfDaysToSkip = 1)
    {
        $nextDate = $currentDate;
        $skippedDays = 0;
        while ($skippedDays < $numberOfDaysToSkip) {
            $nextDate = $nextDate->add(new \DateInterval('P1D')); // Add one day

            // Check if the next date is a working day
            $weekday = $nextDate->format('N'); // 1 (Monday) to 7 (Sunday)

            // Assuming that Saturday (6) and Sunday (7) are non-working days
            if ($weekday < 6) {
                $skippedDays++;
            }
        }
        return $nextDate->format('l, d F Y');
    }

    /**
     * Get Start Date exclude holiday function
     *
     * @param mixed $startDate
     * @param array $holidays
     * @return void
     */
    public function getStartDate($startDate, $holidays)
    {
        $add_day = 0;

        do {
            $new_date = date('Y-m-d', strtotime("$startDate +$add_day Days"));
            $add_day++;
            $new_day_of_week = date('w', strtotime($new_date));
        } while (in_array($new_day_of_week, $holidays));

        $startDate = $new_date;
        return $startDate;
    }
}
