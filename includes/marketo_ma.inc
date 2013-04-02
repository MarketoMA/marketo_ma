<?php

/**
 * @file
 * Defines constants used by the marketo ma module
 */

define('MARKETO_MA_PAGES', "admin\nadmin/*\nbatch\nnode/add*\nnode/*/*\nuser/*/*");
define('MARKETO_MA_TRACKING_METHOD_DEFAULT', 'munchkin');
define('MARKETO_MA_WEBFORM_FIELD_DEFAULTS', "FirstName|First Name\nLastName|Last Name\nEmail|Email Address");

define('MARKETO_MA_SCHEMA_WEBFORM', 'marketo_ma_webform');
define('MARKETO_MA_WEBFORM_FIELD_ACTIVE', 'is_active');
define('MARKETO_MA_WEBFORM_FIELD_LEAD_SOURCE', 'lead_source');

define('MARKETO_MA_SCHEMA_WEBFORM_COMPONENT', 'marketo_ma_webform_component');
define('MARKETO_MA_WEBFORM_COMPONENT_KEY', 'marketo_ma_key');
define('MARKETO_MA_WEBFORM_COMPONENT_NONE', 'none');
