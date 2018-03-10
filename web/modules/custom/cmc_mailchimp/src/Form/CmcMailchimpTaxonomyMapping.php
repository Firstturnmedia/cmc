<?php
namespace Drupal\cmc_mailchimp\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Serialization\Yaml;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CmcMailchimpTaxonomyMapping
 */
class CmcMailchimpTaxonomyMapping extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cmc_mailchimp_taxonomy_mapping';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get all mailchimp lists from mc api
    $lists = $this->getMailchimpLists();
    //
    $mcapi = mailchimp_get_api_object('MailchimpLists');
    // Get config for default values
    $config = $this->config('cmc_mailchimp.taxonomy_mapping')->getRawData();

    foreach ($lists as $list) {
      // MC lists
      $form[$list->id] = [
        '#type' => 'details',
        '#title' => t('Mailchimp List: ' . $list->name),
        '#open' => TRUE,
        '#tree' => TRUE,
      ];

      // MC "interest categories"
      $interest_categories_result = $mcapi->getInterestCategories($list->id);

      if ($interest_categories_result->total_items > 0) {
        $interest_categories = $interest_categories_result->categories;

        foreach ($interest_categories as $interest_category) {
          //
          $form[$list->id][$interest_category->id . '_details'] = [
            '#type' => 'details',
            '#title' => t('Mailchimp Interest Category: ' . $interest_category->title),
            '#open' => TRUE,
            //'#tree' => TRUE,
          ];

          // MC interest category -> taxoomy vocab
          $form[$list->id][$interest_category->id . '_details'][$interest_category->id] = [
            '#type' => 'select',
            '#title' => t($interest_category->title . ' [' . $interest_category->id . ']'),
            '#description' => t('Select the taxonomy vocab to map to this mailchimp interest category'),
            '#options' => $this->getTaxonomyVocabsAsSelectOptions(),
            '#default_value' => $config[$list->id][$interest_category->id]['vid'],
            '#empty_value' => '',
            '#empty_option' => '- Select Taxonomy Vocabulary -',
          ];

          $interests_result = $mcapi->getInterests($list->id, $interest_category->id);

          if ($interests_result->total_items > 0) {
            $interests = $interests_result->interests;

            foreach ($interests as $interest) {
              // MC interest -> taxoomy term
              $form[$list->id][$interest_category->id . '_details'][$interest->id] = [
                '#type' => 'select',
                '#title' => t($interest->name . ' [' . $interest->id . ']'),
                '#description' => t('Select the taxonomy term to map to this mailchimp interest'),
                '#options' => $this->getTaxonomyTermsAsSelectOptions(),
                '#default_value' => $config[$list->id][$interest_category->id]['tid_mappings'][$interest->id],
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

    //$debug = $values;


    $mapping = [
      'ba1e8e8bdb' => [
        '2a71d672c9' => [
          'vid' => 'interests',
          'tid_mappings' => [
            'fea1615ce0' => 1,
            '0babb9ccfb' => 2,
            '66d76b4979' => 3
          ],
        ],
        '92a0ea5bfc' => [
          'vid' => 'volunteerism_engagement',
          'tid_mappings' => [
            '6f8983461a' => 6,
            '00d072a55d' => 5,
            '987237e289' => 4
          ],
        ],
      ],
    ];

    // Get config object
    $config = \Drupal::configFactory()->getEditable('cmc_mailchimp.taxonomy_mapping');
    // Set data
    $config->setData($mapping);
    // Save config
    $config->save(TRUE);

    drupal_set_message('Taxonomy mappings have been saved.');
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
