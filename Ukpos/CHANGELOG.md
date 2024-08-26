# Change Log

## [1.0.0] - 2024-08-26

### Added
- Implemented `Ahmed_Ukpos` Magento 2 module to calculate the earliest possible delivery date based on various factors.
- Added configuration fields to Magento Admin for:
  - Delivery Days (`multiselect`)
  - Delivery Exception Dates (`textarea`)
  - Delivery Time (`text`)
  - Cut-Off Time (`time`)
- Plugin for `ShippingMethodConverter` to append the delivery date to the shipping method title based on the calculated earliest delivery date.
- Logic to calculate the nearest delivery date considering:
  - Delivery days allowed (Weekdays, Saturday, Sunday)
  - Delivery exception dates
  - Dispatch days allowed (Weekdays, Saturday, Sunday)
  - Dispatch cut-off time

### Fixed
- Properly calculates delivery dates, avoiding delivery and dispatch exceptions.

### Notes
- Configuration values must be updated to reflect the warehouse and delivery method specifics.
