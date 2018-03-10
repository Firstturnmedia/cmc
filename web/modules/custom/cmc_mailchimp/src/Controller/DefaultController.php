<?php

namespace Drupal\cmc_mailchimp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\cmc_redhen_activity\Entity\Activity;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\field\Entity\FieldConfig;

/**
 * Class DefaultController.
 */
class DefaultController extends ControllerBase {

  /**
   * Hello.
   *
   * @return string
   *   Return Hello string.
   */
  public function test() {
    /*$list_id = 'ba1e8e8bdb';
    $email = 'test@williamluisi6.com';
    $interests = [
      // Interests
      '2a71d672c9' => [
        // Political
        'fea1615ce0' => 'fea1615ce0',
      ],
    ];

    $result = mailchimp_subscribe($list_id, $email, $merge_vars = NULL, $interests, $double_optin = FALSE, $format = 'html');
    */

    $contact_id = 2;
    $contact = entity_load('redhen_contact', $contact_id);

    $field_tags = $contact->get('field_tags')->getValue();

    foreach ($field_tags as $field_tag) {
      $tids[] = $field_tag['target_id'];
    }

    $debug = $contact->get('field_mailchimp')->getValue();

    // Get mc list id from contact field
    $field = FieldConfig::loadByName($entity_type = 'redhen_contact', $bundle_name = 'individual', $field_name = 'field_mailchimp');
    $mc_list_id = $field->getFieldStorageDefinition()->getSetting('mc_list_id');

    $cmc_mc_tax_mapping = \Drupal::config('cmc_mailchimp.taxonomy_mapping')->get();

    $interest_groups = [];

    foreach ($cmc_mc_tax_mapping as $list) {
      foreach ($list as $interest_group_id => $interest_group) {
        $interest_groups[$interest_group_id] = $interest_group['tid_mappings'];
      }
    }

    // 2, 5, 1
    $debug = $tids;

    //
    foreach ($interest_groups as $interest_group_id => $interest_group) {
      foreach ($interest_group as $interest_id => $tid) {
        if (!in_array($tid, $tids)) {
          print $tid . ' ';
          // Change values to match keys, which is the format MC wants
          //$interest_groups[$interest_group_id][$interest_id] = $interest_id;
          //$debug = $interest_groups[$interest_group_id][$interest_id];

          // unset any items that are not in the tids array
          unset($interest_groups[$interest_group_id][$interest_id]);
        }
        else {
          // Change values to match keys, which is the format MC wants
          $interest_groups[$interest_group_id][$interest_id] = $interest_id;
        }
      }
    }

    $debug = $interest_groups;


    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: test'),
    ];
  }

  private function getInterestFromTid($tid) {

  }

}
