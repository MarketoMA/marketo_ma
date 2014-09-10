<?php
/**
 * @file
 * Hooks provided by Marketo MA.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * This hook is executed when a lead is added to the queue for submission.
 *
 * @param array $data
 *   An associative array containing lead data
 *   - email: The email address of this lead
 *   - data: An associative array containing marketo fields and their values
 *     - FirstName
 *     - LastName
 *   - marketoCookie: NULL or the value of $_COOKIE['_mkto_trk']
 *
 * @see marketo_ma_add_lead()
 */
function hook_marketo_ma_lead_alter(&$data) {
  // Set or update the lead source for this lead.
  $data['data']['LeadSource'] = 'Foo';
}

/**
 * This hook is executed for a specific FIELDNAME when a lead is added to the
 * queue for submission.
 *
 * FIELDNAME equates to valid Marketo field names such as:
 * - FirstName
 * - LastName
 *
 * @param mixed $data
 *   The value of FIELDNAME
 *
 * @see marketo_ma_add_lead()
 */
function hook_marketo_ma_lead_FIELDNAME_alter(&$data) {
  // convert this specific field value to lowercase.
  $data = strtolower($data);
}

/**
 * @} End of "addtogroup hooks".
 */
