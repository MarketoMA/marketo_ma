<?php

namespace Drupal\Tests\marketo_ma_user\FunctionalJavascript;

use Drupal\marketo_ma_user\Service\MarketoMaUserServiceInterface;
use Drupal\Tests\marketo_ma\FunctionalJavascript\MarketoMaJavascriptTestBase;

/**
 * Base for Marketo MA functional javascript tests.
 *
 * @group marketo_ma-js
 */
abstract class MarketoMaUserJavascriptTestBase extends MarketoMaJavascriptTestBase {

  /**
   * The Marketo MA User config.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $marketo_ma_user_config;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'user',
    'encryption',
    'marketo_ma',
    'marketo_ma_user',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->marketo_ma_user_config = \Drupal::configFactory()->getEditable(MarketoMaUserServiceInterface::MARKETO_MA_USER_CONFIG_NAME);

    $all_fields = $this->client->getFields();

    $enabled_fields = [];

    foreach ($all_fields as $marketo_ma_field) {
      if (in_array($marketo_ma_field['default_name'], ['email', 'name'])) {
        $enabled_fields[$marketo_ma_field['id']] = $marketo_ma_field['id'];

        if ($marketo_ma_field['default_name'] === 'email') {
          $this->marketo_ma_user_config
            ->set('mapping', ['mail' => $marketo_ma_field['id']])
            ->save();
        }
      }
    }
    $this->config
      ->set('field.enabled_fields', $enabled_fields)
      ->save();
  }

}
