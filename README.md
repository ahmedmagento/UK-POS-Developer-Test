## Installation

1. **Install the Module**:
   - Place the module directory in `app/code/Ahmed/Ukpos`.

2. **Enable the Module**:
   - Execute the following commands:
     ```bash
     php bin/magento module:enable Ahmed_Ukpos
     php bin/magento setup:upgrade
     php bin/magento setup:di:compile
     php bin/magento cache:clean
     ```

3. **Configuration**:
   - Navigate to `Stores > Configuration > Sales > Shipping Methods` in the Magento Admin panel.
   - Configure the delivery settings for each delivery method under the `Flat Rate` and `Free Shipping` sections.
