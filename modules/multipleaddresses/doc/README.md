# Multiple Addresses


## Overview


This addition allows a logged in user to select from their address history when choosing a billing or shipping address.

In detail:

1. An eZ Publish script uses historical Orders to generate Address records.
2. When placing an order, the user may choose from a list of prior addresses (if they are logged in).
3. When a new order is placed, a workflow task adds the address to the user's record, if it is not already present.


## Recommended index:

Not strictly necessary, but this will make both the batch job to generate addresses and the workflow task to insert them significantly faster.

    create index ndx_ezuser_email_address on ezuser(email);


## Installation:

### Add Extra fields to the Address Content Class

Add the following fields to the Address Content Class.

 * addressee_first_name
 * addressee_last_name
 * street_address_2
 * email
 * phone

If present, remove the following field from the Address Content Class

 * addressee_name


### Prepopulate addresses

Generate the addresses with the following:

    cd <ezpublish-root>
    php extension/xrowecommerce/bin/createaddressesfromorders.php -l admin -p <password> --allow-root-user

This task **will** take significant time. Progress is logged to the console, including a % complete readout.

### Permissions

Allow the Anonymous user full access to the "multipleaddresses" module extension. This allows access to the new fetch functions used on userregister.tpl.

### Workflow task setup

Add the "Save Order Address" workflow task:

 * Log into the admin interface
 * Settings > Workflows
 * Shop > Confirm Order
 * Edit
 * Add Event / Save Order Address
 * OK
