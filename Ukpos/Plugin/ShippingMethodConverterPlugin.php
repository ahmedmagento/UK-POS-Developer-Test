<?php

namespace Ahmed\Ukpos\Plugin;

use Ahmed\Ukpos\Helper\Data as CustomShippingHelper;
/**
 * Class ShippingMethodConverterPlugin
 * 
 * This plugin extends the functionality of the Magento shipping method converter
 * to include dynamic delivery date calculations based on custom configuration settings.
 */

class ShippingMethodConverterPlugin
{
  /**
     * @var CustomShippingHelper
     */
    protected $customShippingHelper;

    /**
     * ShippingMethodConverterPlugin constructor.
     * 
     * @param CustomShippingHelper $customShippingHelper
     */
    public function __construct(CustomShippingHelper $customShippingHelper)
    {
        $this->customShippingHelper = $customShippingHelper;
    }

    /**
     * After plugin method for converting shipping method model to data object.
     * 
     * @param \Magento\Quote\Model\Cart\ShippingMethodConverter $subject
     * @param \Magento\Quote\Api\Data\ShippingMethodInterface $result
     * @param \Magento\Quote\Model\Quote\Address\Rate $rateModel
     * @param string $quoteCurrencyCode
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface
     */
    public function afterModelToDataObject(
        \Magento\Quote\Model\Cart\ShippingMethodConverter $subject,
        $result,
        $rateModel,
        $quoteCurrencyCode
    ) {
        // Determine which carrier is being used
        $carrierCode = $rateModel->getCarrier();

        // Get configuration values based on the carrier code
        if ($carrierCode === 'flatrate') {
            $deliveryDays = $this->customShippingHelper->getFlatRateConfig('delivery_days');
            $deliveryExceptionDates = $this->customShippingHelper->getFlatRateConfig('delivery_exception_dates');
            $deliveryTime = $this->customShippingHelper->getFlatRateConfig('delivery_time');
            $cutOffTime = $this->customShippingHelper->getFlatRateConfig('cut_off_time');
        } elseif ($carrierCode === 'freeshipping') {
            $deliveryDays = $this->customShippingHelper->getFreeShippingConfig('delivery_days');
            $deliveryExceptionDates = $this->customShippingHelper->getFreeShippingConfig('delivery_exception_dates');
            $deliveryTime = $this->customShippingHelper->getFreeShippingConfig('delivery_time');
            $cutOffTime = $this->customShippingHelper->getFreeShippingConfig('cut_off_time');
        }
        // Parse delivery days
        $deliveryDaysArray = explode(',', $deliveryDays);
        $availableDays = $this->parseDeliveryDays($deliveryDaysArray);

        // Parse exception dates
        $exceptionDatesArray = explode("\n", $deliveryExceptionDates);
        $exceptionDates = $this->parseExceptionDates($exceptionDatesArray);
        // Convert the string to a time format
        $cutOffTimeArray = explode(',', $cutOffTime);
        $cutOffTimeFormatted = implode(':', $cutOffTimeArray);
        // Create a DateTime object from the formatted string
        $cutoffTime = \DateTime::createFromFormat('H:i:s', $cutOffTimeFormatted);

        // Calculate the nearest delivery date
        $currentDate = new \DateTime();
        // Check if the cut-off time is set, and if it's not set to '00:00:00'
        if ($cutOffTime && $cutOffTime !== '00:00:00') {
           if ($currentDate->format('H:i:s') > $cutoffTime->format('H:i:s')) {
                // If the current time is after the cut-off time, add one more day to the delivery time
                $deliveryTime++;
            }
        } 

        $nearestDeliveryDate = $this->calculateNearestDeliveryDate($currentDate, $deliveryTime, $availableDays, $exceptionDates);

        // Append the delivery date to the method title
        $methodTitleWithDate = $rateModel->getMethodTitle() . ' - Delivery by ' . $nearestDeliveryDate->format('Y-m-d');
        $result->setMethodTitle($methodTitleWithDate);

        return $result;
    }

    /**
     * Parse delivery days from the multiselect field.
     * @param array $deliveryDaysArray
     * @return array
     */
    private function parseDeliveryDays($deliveryDaysArray)
    {
        $availableDays = [];

        foreach ($deliveryDaysArray as $day) {
            switch ($day) {
                case 'wd':
                    $availableDays = array_merge($availableDays, [1, 2, 3, 4, 5]); // Weekdays
                    break;
                case 'sa':
                    $availableDays[] = 6; // Saturday
                    break;
                case 'su':
                    $availableDays[] = 7; // Sunday
                    break;
            }
        }

        return array_unique($availableDays);
    }

    /**
     * Parse exception dates from the textarea field.
     * @param array $exceptionDatesArray
     * @return array
     */
    private function parseExceptionDates($exceptionDatesArray)
    {
        $exceptionDates = [];

        foreach ($exceptionDatesArray as $date) {
            $date = trim($date);
            if ($date) {
                // Check if the date is valid according to the 'd-m-Y' format
                $dateTime = \DateTime::createFromFormat('d-m-Y', $date);
                $errors = \DateTime::getLastErrors();

                // If the date is valid, add it to the array
                if ($dateTime && $errors['warning_count'] == 0 && $errors['error_count'] == 0) {
                    $exceptionDates[] = $dateTime;
                }
            }
        }

        return $exceptionDates;
    }

    /**
     * Calculate the nearest available delivery date.
     * @param \DateTime $currentDate
     * @param int $deliveryTime
     * @param array $availableDays
     * @param array $exceptionDates
     * @return \DateTime
     */
    private function calculateNearestDeliveryDate($currentDate, $deliveryTime, $availableDays, $exceptionDates)
    {
        $daysAdded = 0;

        while ($daysAdded < $deliveryTime) {
            // Move to the next day
            $currentDate->modify('+1 day');
            $dayOfWeek = (int) $currentDate->format('N');

            // Check if the day is an available delivery day and not an exception date
            if (in_array($dayOfWeek, $availableDays) && !$this->isExceptionDate($currentDate, $exceptionDates)) {
                $daysAdded++;
            }
        }

        // After adding the required delivery days, find the next available day if the current date is unavailable
        while (!in_array((int) $currentDate->format('N'), $availableDays) || $this->isExceptionDate($currentDate, $exceptionDates)) {
            $currentDate->modify('+1 day');
        }

        return $currentDate;
    }

    private function isExceptionDate($currentDate, $exceptionDates)
    {
        foreach ($exceptionDates as $exceptionDate) {
            if ($currentDate->format('Y-m-d') === $exceptionDate->format('Y-m-d')) {
                return true;
            }
        }
        return false;
    }

}
