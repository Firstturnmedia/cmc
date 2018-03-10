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
          $string = 'Contact <i>' . $values['name'] . '</i> has been ' . '<strong>' . $values['op'] . '</strong>';
          break;

        case 'webform':
          $string = 'Contact <i>' . $values['name'] . '</i> <strong>' . $values['op'] . '</strong> a <i>webform</i>';
          break;

        case 'mailchimp':
          if ($values['op'] == 'sent-to') {
            $string = 'Contact <i>' . $values['name'] . '</i> <strong>received</strong> email campaign: ' . $values['resource_id'];
          }
          else if ($values['op'] == 'unsubscribed') {
            $string = 'Contact <i>' . $values['name'] . '</i> was <strong>unsubscribed</strong> from mailchimp';
          }
          else if ($values['op'] == 'open-details') {
            $string = 'Contact <i>' . $values['name'] . '</i> <strong>opened</strong> email campaign: ' . $values['resource_id'];
          }
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
