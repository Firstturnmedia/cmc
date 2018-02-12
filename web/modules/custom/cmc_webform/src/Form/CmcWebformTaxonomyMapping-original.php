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
class CmcWebformTaxonomyMapping extends FormBase{

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
    try {
      //$this->sugarCrm->login();

      $webform = \Drupal::entityTypeManager()->getStorage('webform')
        ->load(\Drupal::request()->get('webform'));
      $elements = $elements = Yaml::decode($webform->get('elements'));

      $debug = $elements;

      $form_state->setStorage(['webformId' => $webform->id(), 'elements' => $elements]);

      // Create component container.
      $form['webform_container'] = array(
        '#prefix' => "<div id=form-ajax-wrapper>",
        '#suffix' => "</div>",
      );

      // Get webform fields and default values for them.
      //$default_values = $this->config('webform_sugarcrm.webform_field_mapping.' . $webform->id())->getRawData();

      foreach ($elements as $key => $element) {
        $selected_module = '_none';
        $selected_field = '_none';

        /*if (!empty($default_values[$key])) {
          $selected_module = $default_values[$key]['sugar_module'];
          $selected_field = $default_values[$key]['sugar_field'];
        }
        */

        $selected_module = !empty($form_state->getValue($key . '_sugarcrm_module')) ?
        $form_state->getValue($key . '_sugarcrm_module') : $selected_module;

        // Create form elements for each Webform field.
        $form['webform_container'][$key] = [
          '#type' => 'fieldset',
          '#title' => $element['#title'],
          '#collapsible' => TRUE,
          '#collapsed' => FALSE,
        ];

        $rows = [];

        $form['webform_container'][$key]['table'] = [
          '#type' => 'item',
          '#markup' => '<table class="cmc_webform-test">'
        ];

        // Get options
        foreach ($element['#options'] as $delta => $item) {
          $form['webform_container'][$delta . '_cmc_webform_answer'] = [
            '#type' => 'item',
            //'#title' => $item,
            '#markup' => $item,
            '#prefix' => '<td>',
            '#suffix' => '</td>',
          ];

          // Build form element here and we render it later
          $form['webform_container'][$delta . '_cmc_webform_taxonomy'] = [
            '#type' => 'entity_autocomplete',
            '#target_type' => 'taxonomy_term',
            '#selection_settings' => [
              'target_bundles' => ['interests', 'volunteerism_engagement'],
            ],
            '#tags' => TRUE,
            '#process_default_value' => FALSE,
            '#prefix' => '<td>',
            '#suffix' => '</td>',
          ];

          // Build rows for table
          /*$rows[$delta] = [
            $item,
            // Render the form element here to inject it properly into the table rows.
            //\Drupal::service('renderer')->render($form['webform_container'][$delta . '_cmc_webform_taxonomy']),
            render($form['webform_container'][$delta . '_cmc_webform_taxonomy']),
          ];*/
        }

        $form['webform_container'][$key]['table']['#markup'] = '<table>';

        // Build table
        /*$form['webform_container'][$key]['table'] = [
          '#type' => 'table',
          '#header' => [t('Answer'), t('Taxonomy Term(s)')],
          '#rows' => $rows,
          '#attributes' => [
            'class' => ['cmc_webform__table'],
          ],
        ];*/
      }

      $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Save'),
      );

      $form['#theme'] = 'cmc_webform_taxonomy_mapping';
    }
    catch (\Exception $e) {
      drupal_set_message($e->getMessage(), 'error');
      return [];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $storage = $form_state->getStorage();

    $data = [];
    foreach ($storage['elements'] as $key => $element) {
      $data[$key] = array(
        'sugar_module' => $values[$key . '_sugarcrm_module'],
        'sugar_field' => $values[$key . '_sugarcrm_field'],
      );
    }

    $config = \Drupal::configFactory()->getEditable('webform_sugarcrm.webform_field_mapping.' . $storage['webformId']);
    $config->setData($data);
    $config->save(TRUE);

    drupal_set_message('Fields mapping have been saved.');
  }

  /**
   * Ajax callback.
   */
  public function formAjaxCallback($form, $form_state) {
    return $form['webform_container'];
  }

  /**
   * Get prepared list of CRM modules.
   *
   *
   * @return mixed
   *   Returns a list of CRM modules.
   */
  private function getModules() {
    $modules = ['_none' => 'None'];

    $crmModules = $this->sugarCrm->getModules();
    if (isset($crmModules->modules)) {
      foreach ($crmModules->modules as $module) {
        $modules[$module->module_key] = $module->module_key;
      }
    }

    return $modules;
  }
  /**
   * Get taxonomy terms
   */
  private function getTaxonomyTerms() {
    // @todo add a config screen for telling webform what taxonomy vocab to use.

    $vid = 'interests';
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);

    foreach ($terms as $term) {
      $term_data[$term->tid] = $term->name;

      /*$term_data[] = [
        'id' => $term->tid,
        'value' => $term->name
      ];*/
    }

    return $term_data;
  }

}
