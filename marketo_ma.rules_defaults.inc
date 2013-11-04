<?php

/**
 * @file
 * Default rules for Marketo MA
 */

/**
 * Implements hook_default_rules_configuration().
 */
function marketo_ma_default_rules_configuration() {
  $config = array();

  $rules = array();
  $rules['rules_update_marketo_lead_on_create'] = <<<EOD
{ "rules_update_marketo_lead_on_create" : {
    "LABEL" : "Update Marketo lead on user creation",
    "PLUGIN" : "reaction rule",
    "ACTIVE" : false,
    "TAGS" : [ "Marketo" ],
    "REQUIRES" : [ "rules", "marketo_ma" ],
    "ON" : [ "user_insert" ],
    "DO" : [
      { "variable_add" : {
          "USING" : { "type" : "list\u003Ctext\u003E" },
          "PROVIDE" : { "variable_added" : { "marketoparams" : "Marketo Parameters" } }
        }
      },
      { "list_add" : { "list" : [ "marketoparams" ], "item" : "Email|[account:mail]" } },
      { "marketo_ma_rules_action_add_lead" : { "account" : [ "account" ], "data" : [ "marketoparams" ] } }
    ]
  }
}
EOD;

  $rules['rules_update_marketo_lead_on_login'] = <<<EOD
{ "rules_update_marketo_lead_on_login" : {
    "LABEL" : "Update Marketo lead on login",
    "PLUGIN" : "reaction rule",
    "ACTIVE" : false,
    "TAGS" : [ "Marketo" ],
    "REQUIRES" : [ "rules", "marketo_ma" ],
    "ON" : [ "user_login" ],
    "DO" : [
      { "variable_add" : {
          "USING" : { "type" : "list\u003Ctext\u003E" },
          "PROVIDE" : { "variable_added" : { "marketoparams" : "Marketo Parameters" } }
        }
      },
      { "list_add" : { "list" : [ "marketoparams" ], "item" : "Email|[account:mail]" } },
      { "marketo_ma_rules_action_add_lead" : { "account" : [ "site:current-user" ], "data" : [ "marketoparams" ] } }
    ]
  }
}
EOD;

  $rules['rules_update_marketo_lead_on_user_update'] = <<<EOD
{ "rules_update_marketo_lead_on_user_update" : {
    "LABEL" : "Update Marketo lead on user update",
    "PLUGIN" : "reaction rule",
    "ACTIVE" : false,
    "TAGS" : [ "Marketo" ],
    "REQUIRES" : [ "rules", "marketo_ma" ],
    "ON" : [ "user_update" ],
    "DO" : [
      { "variable_add" : {
          "USING" : { "type" : "list\u003Ctext\u003E" },
          "PROVIDE" : { "variable_added" : { "marketoparams" : "Marketo Parameters" } }
        }
      },
      { "list_add" : { "list" : [ "marketoparams" ], "item" : "Email|[account:mail]" } },
      { "marketo_ma_rules_action_add_lead" : { "account" : [ "account" ], "data" : [ "marketoparams" ] } }
    ]
  }
}
EOD;

  foreach ($rules as $key => $value) {
    $config[$key] = rules_import($value);
  }

  return $config;
}
