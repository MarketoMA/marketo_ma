<?php

namespace Drupal\Tests\marketo_ma\Kernel;

use Drupal\marketo_ma\Service\MarketoMaMunchkinInterface;

/**
 * @group marketo_ma
 */
class MarketoMaMunchkinServiceTest extends MarketoMaKernelTestBase {

  /**
   * @var \Drupal\marketo_ma\Service\MarketoMaMunchkinInterface
   *
   * The marketo_ma munchkin service.
   */
  protected $munchkin;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'user',
    'encryption',
    'marketo_ma',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Set up required settings.
    $this->config
      ->set('tracking_method', 'munchkin')
      ->save();

    // Get the API client service.
    $this->munchkin = \Drupal::service('marketo_ma.munchkin');
  }

  /**
   * Tests the marketo_ma service.
   */
  public function testMarketoMaService() {
    self::assertTrue($this->munchkin instanceof MarketoMaMunchkinInterface);

    self::assertTrue($this->munchkin->isConfigured());
    self::assertEquals(getenv('marketo_ma_munchkin_account_id'), $this->munchkin->getAccountID());
  }

}
