<?php

namespace Drupal\cmc_mailchimp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Url;

/**
 * Class GroupsOverview.
 */
class GroupsOverview extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function content() {
    // Build a table of each MC list with a link to the groups config pg.
    $header = [
      'Mailchimp List',
      'Group Mapping Link'
    ];

    // Get all mailchimp lists from mc api
    $lists = $this->getMailchimpLists();

    foreach ($lists as $list) {
      // Get url
      $url = Url::fromRoute('cmc_mailchimp.groups', array('mc_list_id' => $list->id));

      // Build table rows
      $rows[] = [
        $list->name,
        \Drupal::l(t('Configure Groups Mapping'), $url)
      ];
    }

    $build = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No data to display'),
    ];

    return $build;
  }

  /**
   * Helper function to get all mailchimp lists
   */
  private function getMailchimpLists() {
    // Get all mailchimp lists from mc api
    $mcapi = mailchimp_get_api_object('MailchimpLists');
    if ($mcapi != null) {
      $result = $mcapi->getLists();

      if ($result->total_items > 0) {
        $lists = $result->lists;
      }
    }

    return $lists;
  }

}
