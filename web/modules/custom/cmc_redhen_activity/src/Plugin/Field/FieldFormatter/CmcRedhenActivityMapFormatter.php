<?php

namespace Drupal\cmc_redhen_activity\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'Random_default' formatter.
 *
 * @FieldFormatter(
 *   id = "map_formatter",
 *   label = @Translation("Map field formatter"),
 *   field_types = {
 *     "map"
 *   }
 * )
 */
class CmcRedhenActivityMapFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $settings = $this->getSettings();

    $summary[] = t('Map base field formatter.');

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $values = $item->getValue();

      switch($values['type']) {
        case 'redhen_contact':
          $string = 'Contact ' . $values['name'] . ' has been ' . $values['op'];
          break;

        case 'webform':
          $string = 'Contact ' . $values['name'] . ' ' . $values['op'] . ' a webform';
          break;
      }

      $element[$delta] = [
        '#type' => 'markup',
        '#markup' => $string,
      ];
    }

    return $element;
  }

}
