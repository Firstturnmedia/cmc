<?php

namespace Drupal\cmc_mailchimp\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 *
 * @Block(
 *   id = "cmc_mailchimp_contact_report",
 *   admin_label = @Translation("Mailchimp Activity"),
 * )
 */
class ContactReport extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $contact = \Drupal::routeMatch()->getParameter('redhen_contact');

    if ($contact instanceof \Drupal\redhen_contact\ContactInterface) {
      $contact_id = $contact->id();

      $email_open_count = $this->getActivityCount($contact_id, $mailchimp_type = 'open-details');
      $email_open_sent = $this->getActivityCount($contact_id, $mailchimp_type = 'sent-to');

      // Set default open percent to 0
      $email_open_percent_formatted = 0;

      if ($email_open_count > 0 && $email_open_sent > 0) {
        $email_open_percent = $email_open_sent / $email_open_count;
        $email_open_percent_formatted = number_format($email_open_percent * 100, 0);
      }

      // @todo this seems unecessary given the tpl doesnt use these vars?
      $data = [
        'emails_open' => $email_open_percent_formatted,
        'emails_sent' => $email_open_sent,
      ];

      return [
        '#theme' => 'cmc_mailchimp_contact_report',
        // @todo this fully disabled cache for this block, add proper cache context
        '#cache' => [
          'max-age' => 0,
        ],
        '#data' => $data,
        '#attached' => [
          'drupalSettings' => [
            'emails_open' => $data['emails_open'],
            'emails_sent' => $data['emails_sent'],
          ],
          'library' => [
            'cmc_mailchimp/cmc_mailchimp',
          ],
        ],
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  private function getActivityCount($contact_id, $mailchimp_type) {
    $query = \Drupal::entityQuery('cmc_redhen_activity')
      ->condition('status', 1)
      ->condition('contact_id', $contact_id)
      ->condition('field_mailchimp_type.value', $mailchimp_type)
      ->condition('type', 'mailchimp');
    $count = $query->count()->execute();

    return $count;
  }
}
