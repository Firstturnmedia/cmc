<?php

namespace Drupal\cmc_mailchimp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Url;

/**
 * Class ContactReport.
 */
class ContactReport extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function content() {


    return [
      '#theme' => 'cmc_mailchimp_contact_report',
      '#data' => $this->t('Test Value'),
      '#attached' => [
        'library' => [
          'cmc_mailchimp/cmc_mailchimp',
        ],
      ],
    ];
  }
}
