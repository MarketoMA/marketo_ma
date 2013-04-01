<?php

/**
 * @file
 * Defines constants used by the marketo module
 */

define('MARKETO_PAGES', "admin\nadmin/*\nbatch\nnode/add*\nnode/*/*\nuser/*/*");
define('MARKETO_TRACKING_METHOD_DEFAULT', 'munchkin');
define('MARKETO_WEBFORM_FIELD_DEFAULTS', "FirstName|First Name\nLastName|Last Name\nEmail|Email Address");

define('MARKETO_SCHEMA_WEBFORM', 'marketo_webform');
define('MARKETO_WEBFORM_FIELD_ACTIVE', 'is_active');
define('MARKETO_WEBFORM_FIELD_LEAD_SOURCE', 'lead_source');

define('MARKETO_SCHEMA_WEBFORM_COMPONENT', 'marketo_webform_component');
define('MARKETO_WEBFORM_COMPONENT_KEY', 'marketo_key');
define('MARKETO_WEBFORM_COMPONENT_NONE', 'none');
