<?php

namespace Drupal\Tests\marketo_ma\Unit;

use Drupal\marketo_ma\Lead;


/**
 * @coversDefaultClass \Drupal\marketo_ma\Lead
 * @group marketo_ma
 */
class LeadUnitTest extends \PHPUnit_Framework_TestCase {

  public $lead_data;

  public function setUp() {
    parent::setUp();

    $this->lead_data = [
      'id' => '12',
      'email' => 'mail@example.org',
      'firstName' => 'Fancy first name',
      'lastName' => 'Fancy last name',
      'cookies' => 'ILoveCookies',
    ];

  }

  public function testLead() {
    $lead = new Lead($this->lead_data);

    self::assertEquals($this->lead_data['id'], $lead->id());
    self::assertEquals($this->lead_data['email'], $lead->getEmail());
    self::assertEquals($this->lead_data['cookies'], $lead->getCookie());

    $new_lead_data = ['id' => '13'] + $this->lead_data;

    self::assertEquals($new_lead_data, $lead->set('id', '13')->data());

    self::assertEquals('Cookies!!!!!', $lead->setCookie('Cookies!!!!!')->getCookie());

  }

  public function testSerialization() {

    $lead = new Lead($this->lead_data);

    $serialized_lead = serialize($lead);

    $unserialized_lead = unserialize($serialized_lead);

    self::assertEquals($lead, $unserialized_lead);
  }

}
