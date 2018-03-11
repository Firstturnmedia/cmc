<?php
namespace Drupal\cmc_mailchimp\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Serialization\Yaml;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CmcMailchimpGroups
 */
class CmcMailchimpGroups extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cmc_mailchimp_groups';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $mc_list_id = NULL) {
    // Set mc list id into storage for use in submit handler
    $form_state->setStorage(['mc_list_id' => $mc_list_id]);

    //
    $mcapi = mailchimp_get_api_object('MailchimpLists');

    if ($mcapi != null) {
      // Get config for default values
      $config = $this->config('cmc_mailchimp.groups_mapping.' . $mc_list_id)->getRawData();

      // MC "interest categories"
      $interest_categories_result = $mcapi->getInterestCategories($mc_list_id);

      if ($interest_categories_result->total_items > 0) {
        $interest_categories = $interest_categories_result->categories;

        foreach ($interest_categories as $interest_category) {
          //
          $form[$interest_category->id . '_details'] = [
            '#type' => 'details',
            '#title' => t('Mailchimp Interest Category: ' . $interest_category->title),
            '#open' => TRUE,
            '#tree' => TRUE,
          ];

          // MC interest category -> taxoomy vocab
          $form[$interest_category->id . '_details'][$interest_category->id] = [
            '#type' => 'select',
            '#title' => t($interest_category->title . ' [' . $interest_category->id . ']'),
            '#description' => t('Select the taxonomy vocab to map to this mailchimp interest category'),
            '#options' => $this->getTaxonomyVocabsAsSelectOptions(),
            '#default_value' => $config[$interest_category->id]['vid'],
            '#empty_value' => '',
            '#empty_option' => '- Select Taxonomy Vocabulary -',
          ];

          $interests_result = $mcapi->getInterests($mc_list_id, $interest_category->id);

          if ($interests_result->total_items > 0) {
            $interests = $interests_result->interests;

            foreach ($interests as $interest) {
              // MC interest -> taxoomy term
              $form[$interest_category->id . '_details'][$interest->id] = [
                '#type' => 'select',
                '#title' => t($interest->name . ' [' . $interest->id . ']'),
                '#description' => t('Select the taxonomy term to map to this mailchimp interest'),
                '#options' => $this->getTaxonomyTermsAsSelectOptions(),
                '#default_value' => $config[$interest_category->id]['tids'][$interest->id],
                '#empty_value' => '',
                '#empty_option' => '- Select Taxonomy Term -',
              ];
            }
          }
        }
      }
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->cleanValues()->getValues();
    $storage = $form_state->getStorage();

    // Alter array to match the config array structure we want.
    foreach ($values as $key => $interest_category) {
      // Remove _details from keys
      $interest_category_id = str_replace('_details', '', $key);
      $values[$interest_category_id] = $interest_category;

      // Unset original _details array values
      unset($values[$key]);

      // Set vid as key
      foreach ($interest_category as $key => $value) {
        if ($key === $interest_category_id) {
          // Set vid as key
          $values[$interest_category_id]['vid'] = $value;
          // Unset original array value
          unset($values[$interest_category_id][$interest_category_id]);
        }
        // Move tids into 'tids'
        else {
          $values[$interest_category_id]['tids'][$key] = $value;
          // Unset
          unset($values[$interest_category_id][$key]);
        }
      }
    }

    // Get config object
    $config = \Drupal::configFactory()->getEditable('cmc_mailchimp.groups_mapping.' . $storage['mc_list_id']);
    // Set data
    $config->setData($values);
    // Save config
    $config->save(TRUE);
    //
    drupal_set_message('Groups mapping has been saved!');
  }

  /**
   * Helper function to get taxonomy vocabs as select options
   */
  private function getTaxonomyVocabsAsSelectOptions() {
    // Load all taxonomy vocabs
    $taxonomy_vocabularies = \Drupal\taxonomy\Entity\Vocabulary::loadMultiple();

    // Build an array of form options
    foreach ($taxonomy_vocabularies as $key => $value) {
      $taxonomy_vocab_options[$key] = $value->label();
    }

    return $taxonomy_vocab_options;
  }

  /**
   * Helper function to get taxonomy terms
   */
  private function getTaxonomyTermsAsSelectOptions() {
    $query = \Drupal::entityQuery('taxonomy_term');
    $tids = $query->execute();

    foreach ($tids as $tid) {
      $term = \Drupal\taxonomy\Entity\Term::load($tid);
      $options[$tid] = $term->name->value;
    }

    return $options;
  }

}
