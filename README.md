# Marketo MA

This module adds Marketo tracking capability to your Drupal site.

## Requirements

- Drupal 7.x
- An active Marketo account http://www.marketo.com/

## Installation

1. Copy the entire marketo_ma directory the Drupal sites/all/modules directory.

2. Login as an administrator. Enable the Marketo MA module in
   "Administer" -> "Modules"

3. (Optional) Enable the Marketo MA User module in
   "Administer" -> "Modules"

4. (Optional) Enable the Marketo MA Webform module in
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

- **REST API (Synchronous)**
  Lead data will be sent to Marketo immediately as part of a page request but 
  may increase page load time.

- **REST API (Asynchronous)**
  Lead updates are added to a queue when captured and data is sent to
  Marketo each time cron runs. Ensure you are running cron regularly.

**Munchkin Javascript API - API Private Key**
Your Munchkin API Private key. This can be set/retrieved on your Marketo
admin site under Admin > Integration > Munchkin > API Configuration.
Additional information can be found in the Marketo article
[Enable Munchkin API Use](http://developers.marketo.com/documentation/websites/munchkin-api/).

**Munchkin Javascript API - Partition**
This currently does nothing and is ignored.

**REST API**
Values for these fields can be set/retrieved on your Marketo admin site under
Admin > Integration > REST API. Additional information can be found in the
Marketo article [Configuring Your REST API Settings](http://developers.marketo.com/documentation/rest/).

- REST endpoint
- REST identity
- Client ID
- Client Secret

REST configuration will be validated upon save.

**REST API - Proxy Settings**
Proxy settings can be set if your server needs to use a proxy for external requests.

### <a id="field-definition"></a> Field Definition

The fields configured here will be available for mapping to User and Webform fields.
They should match those that are defined in your Marketo admin under
Admin > Field Management. Additional information regarding Marketo fields can be
found in the Marketo articles [Field Management](http://docs.marketo.com/display/public/DOCS/Field+Management)
and [Export a List of All Marketo API Field Names](http://docs.marketo.com/display/public/DOCS/Export+a+List+of+All+Marketo+API+Field+Names).

**Marketo Fields**
This section should contain a pipe "|" delimited list of the fields in the format
"[Field API Name]|[Friendly Label]". Example:
    
    firstName|First Name
    lastName|Last Name
    email|Email Address

### <a id="page-visibility"></a> Page Visibility

Configure options for which pages should be tracked or excluded from tracking.

**Add tracking to specific pages**
This option defines the default rule for tracking pages

- All pages except those listed below
- Only the pages listed below

Specify pages by entering their paths, one path per line, and optionally using
a \* as a wildcard. The default setting is:

    admin
    admin/*
    batch
    node/add*
    node/*/*
    user/*/*

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

2. Ensure that REST API settings are configured in the API Configuration 
   section. User integration is dependant on the REST API and will leverage it 
   regardless of which tracking method you have selected.

3. In the User Integration section you will find options for activity triggers
   as well as field mappings. It is recommended that you select all of the
   options under "Trigger a lead update on the following events".

4. In the User Field Mapping section you will see a table of all custom fields
   which have been added for accounts. Use the select boxes in the Marketo
   column to map each field. The options available to you are defined in the
   "Field Definition" section of the the Marketo MA config screen.

5. Save configuration

**It is not necessary to define a mapping for email address as this field is automatically mapped to the Marketo "Email" field.**

### <a id="webform-integration"></a> Webform Integration (Requires Marketo MA Webform)

Marketo settings for a given webform are managed on each individual node. This
section displays a table of all content on the site that may have a webform
attached to it and provides an overview of each item's current state.

- **Title**
  The title of the webform.
  
- **Tracking Enabled**
  A Yes/No value indicating if tracking is currently enabled for this webform.
  
- **Components Mapped**
  The number of form components that have been mapped to a Marketo field.
  
- **Manage**
  A direct link to the Marketo component mapping page for this webform.

## Configuring a Webform for tracking

These steps assume you already have a webform and components defined. Instructions for
creating a webform can be found here http://drupal.org/documentation/modules/webform.

1. Ensure the Marketo MA Webform module is enabled.

2. From the Webform Integration section of the Marketo MA module configuration page,
   click the Edit link for the webform you would like to configure. Alternatively you
   can view the webform node directly and click the Marketo link found on the Webform
   tab. The direct path to the setup page will be node/%nodeid/webform/marketo.

3. Enable or disable lead capture for this webform by toggling the "Capture Data"
   checkbox. When this setting is unchecked, no data will be sent to Marketo.

4. Assuming you have defined components for this webform you will see a table of
   all components and their associated Marketo mappings. Use the select boxes in
   the Marketo column to map each field. The options available to you are defined
   in the "Field Definition" section of the the Marketo MA config screen.
   **At a minimum must have a field mapped to the Marketo Email if you want data to be captured.**

5. Save the form
