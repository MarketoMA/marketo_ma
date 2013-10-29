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
      { "list_add" : { "list" : [ "marketoparams" ], "item" : "mail|[account:mail]" } },
      { "list_add" : {
          "list" : [ "marketoparams" ],
          "item" : "site_lead_source|[site:current-page:url]"
        }
      },
      { "list_add" : {
          "list" : [ "marketoparams" ],
          "item" : "FirstName|[site:current-user:field-first-name]"
        }
      },
      { "list_add" : {
          "list" : [ "marketoparams" ],
          "item" : "LastName|[site:current-user:field-last-name]"
        }
      },
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
      { "list_add" : { "list" : [ "marketoparams" ], "item" : "mail|[account:mail]" } },
      { "list_add" : {
          "list" : [ "marketoparams" ],
          "item" : "FirstName|[account:field-first-name]"
        }
      },
      { "list_add" : {
          "list" : [ "marketoparams" ],
          "item" : "LastName|[account:field-last-name]"
        }
      },
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
