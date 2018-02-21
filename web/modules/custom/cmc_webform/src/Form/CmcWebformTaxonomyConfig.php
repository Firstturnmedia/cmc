<?php

namespace Drupal\cmc_webform\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Serialization\Yaml;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CmcWebformTaxonomyConfig
 *
 * @package Drupal\webform_sugarcrm\Form
 */
class CmcWebformTaxonomyConfig extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cmc_webform_taxonomy_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get webform
    $webform = \Drupal::entityTypeManager()->getStorage('webform')
      ->load(\Drupal::request()->get('webform'));

    // This sets the webform id value in storage for form submit
    $form_state->setStorage(['webformId' => $webform->id()]);

    // Get taxonomy vocabs
    $taxonomy_vocab_options = $this->getTaxonomyVocabsAsOptions();

    // Get webform fields and default values for them.
    $default_values = $this->config('cmc_webform.taxonomy_config.' . $webform->id())->getRawData();

    $form['taxonomy_vocabs'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Taxonomy Vocabularies'),
      '#description' => $this->t('Select the taxonomy vocabularies to associate with this webform.'),
      '#options' => $taxonomy_vocab_options,
      '#required' => FALSE,
      '#default_value' => $default_values,
    ];

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get form state values and storage
    $values = $form_state->cleanValues()->getValues();
    $storage = $form_state->getStorage();

    // Get taxonomy vocabs
    $taxonomy_vocabs = $values['taxonomy_vocabs'];

    $data = [];
    foreach ($taxonomy_vocabs as $item) {
      if ($item) {
        $data[] = $item;
      }
    }

    // Save config
    $config = \Drupal::configFactory()->getEditable('cmc_webform.taxonomy_config.' . $storage['webformId']);
    // Set data
    $config->setData($data);
    // Save config
    $config->save(TRUE);

    drupal_set_message('Taxonomy config has been saved.');
  }

  /**
   * Replace spaces
   */
  private function getTaxonomyVocabsAsOptions() {
    // Load all taxonomy vocabs
    $taxonomy_vocabularies = \Drupal\taxonomy\Entity\Vocabulary::loadMultiple();

    // Build an array of form options
    foreach ($taxonomy_vocabularies as $key => $value) {
      $taxonomy_vocab_options[$key] = $value->label();
    }

    return $taxonomy_vocab_options;
  }
}
