Magento 2 - Abandoned Cart Reminder
 ======
 
[![License: MPL 2.0](https://img.shields.io/badge/License-MPL%202.0-brightgreen.svg)](LICENSE)

A module which sends reminder email to customer upon cart abandonment after certain time has passed. User can configure cart abandonment duration in the module configuration as well as view the logs for reminder emails sent in the provided grid in REPORTS menu.

By default, module only sends reminder email to those emails that has given opt-in consent in newsletter entity. This option is configurable in the module configuration, to bypass consented option and send email to all emails irrelevant to consent.

Email template is editable, it can be edited in admin backOffice menu item MARKETING -> Email Templates -> Add New Template -> Load Default Template -> 'AQ Abandoned Carts: Reminder Email'. Newly created template can be used for sending Abandoned Cart reminders by mapping it in module's configuration.

**NEW: Discount Code Feature** - The module now automatically generates unique discount codes for each abandoned cart email, with configurable discount percentage and expiration settings.

## Features

- **Automated Abandoned Cart Detection**: Configurable time-based cart abandonment detection
- **Email Reminder System**: Customizable email templates with cart item details
- **Discount Code Generation**: Automatic unique discount code creation for each abandoned cart
- **Flexible Configuration**: Admin panel settings for all module features
- **Newsletter Integration**: Optional consent-based email sending
- **Email Logging**: Track all sent reminder emails in admin reports

## Requirements

- Magento 2.3+
- Magento module(s) dependency
    - Sales
    - SalesRule
    - Config
    - Quote
    - Catalog
    - Store
- Adeelq core module

## Installation

It is recommended to use [composer](https://getcomposer.org) to install the module.

```bash
composer require adeelq/magento2-abandoned-cart-reminder
```
If you do not use composer, ensure that you also load any dependencies that this module has.

## Configuration

### Basic Abandoned Cart Settings
Navigate to **Stores > Configuration > ADEELQ > Abandoned Cart Reminder: Configuration > Abandoned Cart**

- **Abandoned After**: Set the time period after which a cart is considered abandoned (15 minutes to 24 hours)
- **Send If Consented**: Choose whether to send emails only to customers who have consented to newsletters
- **Email Template**: Select the email template to use for abandoned cart reminders

### Discount Code Settings (NEW)
Navigate to **Stores > Configuration > ADEELQ > Abandoned Cart Reminder: Configuration > Discount Code Settings**

- **Enable Discount Code**: Toggle to enable/disable automatic discount code generation
- **Discount Percentage**: Use the slider to set discount percentage (0-100%)
- **Code Expiration (Hours)**: Set how many hours the discount code remains valid (e.g., 24 for 1 day, 168 for 1 week)
- **Code Prefix**: Customize the prefix for generated discount codes (optional, max 10 characters)

### How Discount Codes Work

1. **Automatic Generation**: When an abandoned cart email is triggered, a unique discount code is automatically generated for each customer/cart combination
2. **Unique Codes**: Each code follows the format: `[PREFIX][CART_ID][TIMESTAMP][RANDOM]` ensuring uniqueness
3. **SalesRule Integration**: Codes are created as Magento Sales Rules with proper constraints:
   - Single use per customer
   - Configurable expiration time
   - Percentage-based discount
   - Store-specific application
4. **Email Integration**: The discount code is prominently displayed in the abandoned cart email with expiration information
5. **Security**: Uses Magento's native coupon system ensuring proper validation and security

## Screenshots
### Configuration page
![config.jpeg](config.jpeg)

### Menu
![menu.jpeg](menu.jpeg)

### Grid page
![list.jpeg](list.jpeg)

### Edit email template
![template.jpeg](template.jpeg)

### Sample abandoned cart reminder email (with discount code)
![email_example.jpeg](email_example.jpeg)

*Note: The email template now includes an optional discount code section that appears when the discount feature is enabled. The discount code is prominently displayed with expiration information.*

## Technical Implementation

### Discount Code Generation Process

1. **Configuration Check**: System checks if discount codes are enabled for the store
2. **Code Generation**: Creates unique alphanumeric codes using format: `[PREFIX][CART_ID][TIMESTAMP][RANDOM]`
3. **Sales Rule Creation**: Automatically creates Magento Sales Rules with:
   - Percentage-based discount (configurable 0-100%)
   - Single use per customer limitation
   - Configurable expiration time
   - Store-specific scope
4. **Email Integration**: Injects discount variables into email template
5. **Security**: Uses Magento's native SalesRule system for validation and redemption

### Email Template Variables (NEW)

The following variables are now available in the abandoned cart email template:

- `{{var discount_code}}` - The generated discount code
- `{{var discount_percentage}}` - The discount percentage value
- `{{var discount_expiration}}` - Formatted expiration date
- `{{var has_discount}}` - Boolean flag for conditional display
- `{{var has_expiration}}` - Boolean flag for conditional expiration display

### Backend Configuration

Admin users can configure the discount system via:
**Stores > Configuration > ADEELQ > Abandoned Cart Reminder: Configuration > Discount Code Settings**

- Interactive slider for discount percentage
- Flexible expiration time in hours
- Customizable code prefix
- Enable/disable toggle with dependent field visibility

## Documents

Download [user guide](guide.pdf) for offline viewing.
