Description
-----------
This module adds Marketo tracking capability to your Drupal site.

Requirements
------------
Drupal 7.x
An active account with Marketo http://www.marketo.com/

Installation
------------
1. Copy the entire marketo_ma directory the Drupal sites/all/modules directory.

2. Login as an administrator. Enable the Marketo MA module in the "Administer" -> "Modules"

3. (Optional) Enable the Marketo MA User module in the "Administer" -> "Modules"

3. (Optional) Enable the Marketo MA Webform module in the "Administer" -> "Modules"


Configuration
------------

Visit admin/config/search/marketo_ma and provide Munchkin and SOAP account information.
This information can be obtained from your Marketo control panel at http://www.marketo.com.

Munchkin Javascript API is the default method for lead capture but options are also
available for SOAP API (see configuration notes below).

If you supply a value for the "Default Lead Source", this value will be sent along with
lead capture data in the LeadSource field.

The setting for "Marketo fields" in the Tracking Options section is primarily used for Webforms.
Once you have configured your SOAP settings properly, you will be able to retrieve this
information directly from Marketo by clicking the "Retrieve from Marketo" button.
This section should contain a pipe "|" delimited list of the fields you would like
to map webform components to. The values supplied here will be available as options
as you define your webform components. The first value should be the Marketo API Name (field)
and the second value should be the Friendly Name to be displayed on screen.


Webform setup
------------

Instructions for creating a webform can be found here http://drupal.org/documentation/modules/webform.

1. To enable Marketo tracking on a webform, visit the webform Form Settings page and
check the "Capture Data" box in the Marketo Options section.

2. In addition to enabling tracking in Form Settings, form components must also be
mapped to a Marketo field. This is done by editing each form component and selecting
a field from the "Map to Marketo field" select box. A value of "- None -" should be
selected if this field should not be sent to Marketo.

** At a minimum you must have a field mapped to Email if you want any data to be captured. **


SOAP API configuration
------------

Marketo MA can also be configured to send lead data to Marketo via the SOAP API. This
option is configured on the main configuration page located at admin/config/search/marketo_ma.

Options available are:
- SOAP API (Synchronous)
  When selected, lead data will be sent to Marketo whenever a record is saved. This
  will result in data being updated immediately but may increase page load time.

- SOAP API (Asynchronous)
  With this option, leads are added to a queue when captured and data is sent to
  Marketo each time cron runs. Ensure you are running cron regularly.
