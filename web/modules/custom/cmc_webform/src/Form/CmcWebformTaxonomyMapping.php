<?php

namespace Drupal\cmc_webform\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Serialization\Yaml;
//use Drupal\webform_sugarcrm\WebformSugarCrmManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class WebformSugarCrmFieldsMapping
 *
 * @package Drupal\webform_sugarcrm\Form
 */
class CmcWebformTaxonomyMapping extends FormBase {

  /**
   * Stores Sugar CRM manager.
   *
   * @var \Drupal\webform_sugarcrm\SugarCrmManager
   */
  /*private $sugarCrm;

  public function __construct(WebformSugarCrmManager $sugarCrm) {
    $this->sugarCrm = $sugarCrm;
  }
  */
  /**
   * {@inheritdoc}
   */
  /*public static function create(ContainerInterface $container) {
    return new static ($container->get('webform_sugarcrm.sugarcrm_manager'));
  }
  */

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cmc_webform_taxonomy_mapping';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get webform
    $webform = \Drupal::entityTypeManager()->getStorage('webform')
      ->load(\Drupal::request()->get('webform'));

    // Decode webform and get webform elements
    $webform_elements = $webform_elements = Yaml::decode($webform->get('elements'));

    // This sets the webform id value in storage for form submit
    $form_state->setStorage(['webformId' => $webform->id(), 'elements' => $webform_elements]);

    // @todo why do we do this?
    // Create component container.
    $form['webform_container'] = array(
      '#prefix' => "<div id=form-ajax-wrapper>",
      '#suffix' => "</div>",
    );

    // Get webform fields and default values for them.
    $cmc_webform_taxonomy_mapping = $this->config('cmc_webform.taxonomy_mapping.' . $webform->id())->getRawData();

    // Get target bundles
    // These are the taxonomy vocabs associated with a webform
    $target_bundles = $this->getTargetBundles($webform->id());

    // $webform_element_key = webform element key, which is more a unique identifier/id
    // This is the "question", i.e., "Favorite NBA Team?", which will get a machine_name
    // type value, such as "favorite_nba_team_", i.e, spaces are removed.
    foreach ($webform_elements as $webform_element_key => $webform_element) {
      // Only give option for pre-set answers.
      // @todo identify other form elements that meet this criteria
      if ($webform_element['#type'] == 'checkboxes') {
        // Create form elements for each Webform field.
        $form['webform_container'][$webform_element_key] = [
          '#type' => 'fieldset',
          '#title' => $webform_element['#title'],
          '#collapsible' => TRUE,
          '#collapsed' => FALSE,
        ];

        // Get webform element options
        // $webform_element_option_value can contain spaces, i.e., "Welcome people at front door"
        // This appears to be causing issues?
        foreach ($webform_element['#options'] as $webform_element_option_value => $webform_element_option_text) {
          // Replace spaces
          $webform_element_option_value = $this->replaceSpaces($webform_element_option_value);

          // Iterate over webform taxonomy mapping and get the tids
          // Build a default values array of term entity objects
          if (isset($cmc_webform_taxonomy_mapping[$webform_element_key][$webform_element_option_value]['tids'])) {
            // Set default_values to array
            $default_values = [];

            // Iterate and build the default values array
            foreach ($cmc_webform_taxonomy_mapping[$webform_element_key][$webform_element_option_value]['tids'] as $tid) {
              // Load the term object
              $term_entity = \Drupal\taxonomy\Entity\Term::load($tid);
              // Build an array of entity objects for the default values
              $default_values[] = $term_entity;

              // @todo add checks here for no terms
            }
          }

          // Webform element option (answer)
          $form['webform_container'][$webform_element_key][$webform_element_option_value . '_cmc_webform_answer'] = [
            '#type' => 'item',
            '#markup' => $webform_element_option_text,
          ];

          // Taxonomy entity autocomplete form element
          // @see https://www.drupal.org/node/2418529
          // 
          $form['webform_container'][$webform_element_key][$webform_element_option_value . '_cmc_webform_taxonomy'] = [
            '#type' => 'entity_autocomplete',
            '#target_type' => 'taxonomy_term',
            '#selection_settings' => [
              'target_bundles' => $target_bundles,
            ],
            '#tags' => TRUE,
            // Needs to be entity object, or array of entity objects
            '#default_value' => $default_values ? $default_values : NULL,
          ];
        }
      }
    }

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save'),
    );

    // Send form to theme/twig template
    //$form['#theme'] = 'cmc_webform_taxonomy_mapping';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get form state values and storage
    $values = $form_state->cleanValues()->getValues();
    $storage = $form_state->getStorage();

    // Set data var to array
    $data = [];

    // Get webform elements keys
    $webform_elements_keys = array_keys($storage['elements']);

    // Iterate over webform elements keys
    foreach ($webform_elements_keys as $webform_element_key) {
      // Only want to do this on webform components with options
      // @todo why not move this above and filter the $webform_elements_keys before?
      if (isset($storage['elements'][$webform_element_key]['#options'])) {
        // Get webform element options values
        $webform_element_options_values = array_keys($storage['elements'][$webform_element_key]['#options']);
        // Iterate
        foreach ($webform_element_options_values as $webform_element_option_value) {
          // Replace spaces
          $webform_element_option_value = $this->replaceSpaces($webform_element_option_value);

          // Check if value is null
          if (is_null($values[$webform_element_option_value . '_cmc_webform_taxonomy'])) {
            continue;
          }
          // Proceed as normal
          else {
            // Build array of term IDs
            $tids = [];
            foreach ($values[$webform_element_option_value . '_cmc_webform_taxonomy'] as $tid) {
              $tids[] = $tid['target_id'];
            }
          }

          // Build final data array
          $data[$webform_element_key][$webform_element_option_value]['tids'] = $tids;
        }
      }
    }

    // Get config object
    $config = \Drupal::configFactory()->getEditable('cmc_webform.taxonomy_mapping.' . $storage['webformId']);
    // Set data
    $config->setData($data);
    // Save config
    $config->save(TRUE);

    drupal_set_message('Taxonomy mappings have been saved.');
  }

  /**
   * Ajax callback.
   */
  public function formAjaxCallback($form, $form_state) {
    return $form['webform_container'];
  }


  /**
   * Get target bundles
   */
  private function getTargetBundles($webform_id) {
    // Hard coded for now, make this dynamic, pulling from config settings per webform
    $target_bundles = ['interests', 'volunteerism_engagement'];

    return $target_bundles;
  }

  /**
   * Replace spaces
   */
  private function replaceSpaces($value) {
    $value = str_replace(' ', '_', $value);
    return $value;
  }

  /**
   * Replace underscores
   */
  private function replaceUnderscores($value) {
    $value = str_replace('_', '', $value);
    return $value;
  }

}
