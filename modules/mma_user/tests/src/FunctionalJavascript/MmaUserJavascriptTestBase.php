<?php

namespace Drupal\Tests\mma_user\FunctionalJavascript;

use Drupal\Tests\marketo_ma\FunctionalJavascript\MmaJavascriptTestBase;

/**
 * Base for Marketo MA functional javascript tests.
 *
 * @group marketo_ma-js
 */
abstract class MmaUserJavascriptTestBase extends MmaJavascriptTestBase {

  /**
   * The Marketo MA User config.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $mma_user_config;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'user',
    'encryption',
    'marketo_ma',
    'mma_user',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->mma_user_config = \Drupal::configFactory()->getEditable('marketo_ma_user.settings');

    $all_fields = $this->client->getFields();

    $enabled_fields = [];

    foreach ($all_fields as $marketo_ma_field) {
      if (in_array($marketo_ma_field['default_name'], ['email', 'name'])) {
        $enabled_fields[$marketo_ma_field['id']] = $marketo_ma_field['id'];

        if ($marketo_ma_field['default_name'] === 'email') {
          $this->mma_user_config
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
