<?php

namespace Drupal\Tests\marketo_ma_contact\Kernel;

use Drupal\contact\Entity\Message;
use Drupal\marketo_ma\Lead;

/**
 * @coversDefaultClass \Drupal\marketo_ma_contact\Hooks\ContactMessageInsert
 * @group marketo_ma_contact
 */
class ContactSubmissionTest extends MarketoMaContactTestBase {

  /**
   *
   */
  public function testContactSubmission() {
    $contact = Message::create([
      'contact_form' => 'test_contact',
      'name' => 'My name',
      'mail' => 'example@example.com',
    ]);
    $contact->save();

    // @todo Potentially one could use a mock instead.
    $synced_leads = \Drupal::service('marketo_ma.api_client')->getSyncedLeads();
    $this->assertEquals([new Lead([
      'firstName' => 'My name',
      'email' => 'example@example.com',
    ]),
    ], $synced_leads);
  }

}
