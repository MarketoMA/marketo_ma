# Marketo MA

This module adds Marketo tracking capability to your Drupal site.

## Requirements

- Drupal 8.x
- Composer <https://getcomposer.org/>
- An active Marketo account <http://www.marketo.com/>

## Installation

1. Copy the entire `marketo_ma` directory the Drupal `modules` directory. You
   can optionally user drush to download with `drush dl marketo_ma`.

2. Add the REST API library to drupal using `composer require
   marketo-api/marketo-rest-client:dev-master`.

3. Login as an administrator. Enable the Marketo MA module in
   "Administer" -> "Modules"

4. (Optional) Enable the Marketo MA User module in
   "Administer" -> "Modules"

5. (Optional) Enable the Marketo MA Webform module in
   "Administer" -> "Modules"


## Module Configuration

The Marketo MA configuration page can be found under Configuration > Search and
metadata > Marketo MA. The direct path is admin/config/search/marketo_ma.

- [API Configuration](#api-configuration)
- [Field Definition](#field-definition)
- [Page Visibility](#page-visibility)
- [Role Visibility](#role-visibility)
- [User Integration](#user-integration)
- [Webform Integration](#webform-integration)

### <a id="api-configuration"></a> Basic Settings and API Configuration

At a minimum, you need to provide your Marketo Account ID and Munchkin API
Private Key. The Munchking API Private key can be set/retrieved on your Marketo
admin site under Admin > Integration > Munchkin > API Configuration.

**Account ID**
Your Marketo account ID

**Munchkin Javascript Library**
Path to munchkin.js. Defaults to //munchkin.marketo.net/munchkin.js and highly
likely that you want to leave it set to the default.

**Default Lead Source**
If you would like all lead updates to include a default Lead Source you can
set it here. If a LeadSource field is defined elsewhere in your lead data,
perhaps as a webform component, it will take precedence over this setting.

**Verbose Logging**
If checked, additional data will be added to watchdog.

**Tracking Method**
Multiple options are available for how captured data is submitted to Marketo.

- **Munchkin Javascript API**
  Lead updates will be sent to Marketo as pages are viewed using the
  client-side Munchkin API.

- **REST API**
  Lead data will be sent to Marketo immediately but may increase page
  load timeunless the `Batch API transactions` option is checked. In that
  case lead updates are added to a queue when captured and data is sent to
  Marketo when cron runs.

**Munchkin Javascript API - API Private Key**
Your Munchkin API Private key. This can be set/retrieved on your Marketo
admin site under Admin > Integration > Munchkin > API Configuration.
Additional information can be found in the Marketo article
[Enable Munchkin API Use](http://community.marketo.com/MarketoTutorial?id=kA250000000Kz4eCAC).

**Munchkin Javascript API - Partition**
This currently does nothing and is ignored.

**REST API**
Values for these fields can be set/retrieved on your Marketo admin site under
Admin > Integration > LaunchPoint. Additional information can be found in the
Marketo article [Create a Custom Service for Use with ReST API](http://docs.marketo.com/display/public/DOCS/Create+a+Custom+Service+for+Use+with+ReST+API).

- Client Id
- Client Secret
- Batch API transactions

**Field Definition**
This section will contain a list of fields that should be enabled for mapping. Field
details include Marketo ID, Display name, REST key and Munchkin key. Check any fields that should be available for mapping. If the list is empty, click the `Retrieve from Marketo` button to fetch the fields from marketo. If the button is grayed out, the REST
API hasn't been configured and field retrieval is unavailable.

### <a id="page-visibility"></a> Page Visiblity

Configure options for which pages should be tracked or excluded from tracking.

**Add tracking to specific pages**
This option defines the default rule for tracking pages

- All pages except those listed below
- Only the pages listed below

Specify pages by entering their paths, one path per line, and optionally using
a \* as a wildcard. The default setting is:

    /admin
    /admin/*
    /batch
    /node/add*
    /node/*/*
    /user/*/*

### <a id="role-visibility"></a> Role Visibility

Configure options for which roles should be tracked or excluded from tracking.

**Add tracking to specific roles**
This option defines the default rule for tracking user roles

- All roles except those selected below
- Only the roles selected below

Roles defined on the site will be available for selection.

### <a id="user-integration"></a> User Integration (Requires Marketo MA User)

The Marketo MA User module allows you to capture and update lead data as users
login and change their profiles. These steps assume you have already configured
additional account fields admin/config/people/accounts/fields.

Management of all integration options are handled on the main Marketo MA
configuration page found at admin/config/search/marketo_ma.

1. Enable the Marketo MA User module.

2. Ensure that REST API settings are configured in the API Configuration section.
   User integration is dependant on the REST API and will leverage it regardless
   of which tracking method you have selected.

3. In the User Integration section you will find options for activity triggers
   as well as field mappings. It is recommended that you select all of the
   options under "Trigger a lead update on the following events".

4. In the User Field Mapping section you will see a table of all custom fields
   which have been added for accounts. Use the select boxes in the Marketo
   column to map each field. The options available to you are defined in the
   "Field Definition" section of the the Marketo MA config screen.

5. Save configuration

**It is not necessary to define a mapping for email address as this field is automatically mapped to the Marketo "Email" field.**

### <a id="contact-integration"></a> Contact form integration (Requires Marketo MA Contact)

Marketo settings for a given contact form are managed on the contact form's
Marketo tab. The following sections are available in the configuration form.

- **Enable tracking**
  A Yes/No value indicating if tracking is currently enabled for this contact form.

- **Field Map**
  This is a table of contact form fields. Each field has a select list to choose
  Marketo field that should be updated when a customer submits the contact form.

## Configuring a contact form for tracking

These steps assume you already have a contact form and fields defined. Instructions for
creating a contact form can be found here <https://www.drupal.org/documentation/modules/contact>.

1. Ensure the Marketo MA Contact module is enabled.

2. On the contact form edit page there will be a tab to configure Marketo MA. Click
   the link or navigate to `admin/structure/contact/{contact_form_id}/marketo`.

3. Enable or disable lead capture for this contact form by toggling the "Capture Data"
   checkbox. When this setting is unchecked, no data will be sent to Marketo.

4. You will see a list of contact form fields with all their associated Marketo
   mappings. Use the select boxes in the Marketo column to map each field. The
   options available to you are defined in the "Field Definition" section of the
   the Marketo MA config page (`admin/config/services/marketo-ma#edit-field-tab`).
   **At a minimum must have a field mapped to the Marketo Email if you want data to be captured.**

5. Save the Marketo MA contact configuration form.
