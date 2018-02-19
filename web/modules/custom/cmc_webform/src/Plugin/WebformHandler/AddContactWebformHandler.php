<?php

namespace Drupal\cmc_webform\Plugin\WebFormHandler;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Serialization\Yaml;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\webform\Element\WebformHtmlEditor;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionConditionsValidatorInterface;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Queue\QueueFactory;

use Drupal\redhen_contact\Entity\Contact;

/**
 * Create internal CRM contact on webform submission
 *
 * @WebformHandler(
 *   id = "add_contact",
 *   label = @Translation("Add Contact"),
 *   category = @Translation("CMC Webform"),
 *   description = @Translation("Creates a Redhen contact from webform submission"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class AddContactWebformHandler extends WebformHandlerBase {
  /**
   * The queue factory.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * The configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelFactoryInterface $logger_factory, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, WebformSubmissionConditionsValidatorInterface $conditions_validator, QueueFactory $queue_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $logger_factory, $config_factory, $entity_type_manager, $conditions_validator);
    $this->queueFactory = $queue_factory;
  }

  /**
   * {@inheritdoc}
  */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory'),
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('webform_submission.conditions_validator'),
      $container->get('queue')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getTids(array $webform_data) {
    // @todo fix this. This code seems insane, but somehow works?

    // Iterate over webform submitted values and build a $build array
    foreach ($webform_data['values'] as $webform_element_key => $webform_submitted_value) {
      // Not all questions [webform_element_key] will be required.
      // Some will be empty values, so we skip those.
      if (!empty($webform_submitted_value)) {
        // Some questions can have multiple values
        // And we only want webform elements with options/multiple values
        if (is_array($webform_submitted_value)) {
          $webform_submitted_options = [];

          // Iterate over the submitted values and build an array
          foreach ($webform_submitted_value as $webform_element_option_value) {
            // Replace spaces w/ underscore to match our webform config
            $webform_submitted_options[] = str_replace(' ', '_', $webform_element_option_value);
          }

          // I dont know what to say here?
          // A multidimensonal array of webform element key + webform submitted options?
          $build[] = [
            'webform_element_key' => $webform_element_key,
            'webform_submitted_options' => $webform_submitted_options
          ];
        }
      }
    }

    // Get webform taxonomy mapping config
    $cmc_webform_taxonomy_mapping = \Drupal::config('cmc_webform.taxonomy_mapping.' . $webform_data['webform_id']);

    // We really only care about the taxonomy terms at this point.
    // We want a simple array of each term id associated w/ users answers.
    foreach ($build as $item) {
      // Iterate over the submitted option
      foreach ($item['webform_submitted_options'] as $webform_submitted_option) {
        // Figure out why some of these are not an array?
        // The returned config value will be an array, but we only want
        // a string value here.
        $config = $cmc_webform_taxonomy_mapping->get($item['webform_element_key'] . '.' . $webform_submitted_option . '.tids');

        if (is_array($config)) {
          $tid = implode($cmc_webform_taxonomy_mapping->get($item['webform_element_key'] . '.' . $webform_submitted_option . '.tids'));
          // Build an array of term ids
          $tids[] = $tid;
        }
        else {
          continue;
        }
      }
    }

    return $tids;
  }

  /**
   * {@inheritdoc}
   */
  public function addContact(array $webform_data) {
    try {
      $contact_type = 'individual';

      // Get taxonomy term ids
      $field_tags_tids = $this->getTids($webform_data);

      // Create contact
      $contact = Contact::create([
        'type' => $contact_type,
        'first_name' => $webform_data['values']['first_name'],
        'last_name' => $webform_data['values']['last_name'],
        'email' => $webform_data['values']['email'],
      ]);

      // Add tags fields
      // Maybe find a better way to do this?
      $contact->set('field_tags', $field_tags_tids);

      // Save contact
      $contact->save();
    }
    // @todo fix exception catching
    catch (EntityStorageException $e) {
      watchdog_exception('webform.add_contact', $e);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    if ($webform_submission->getState() == WebformSubmissionInterface::STATE_COMPLETED) {
      // @see webform/src/WebformSubmissionInterface.php for avail methods

      // @todo seems really hacky to do this?
      // Must be a better way to get the webform_id ?
      $webform_submission_array = $webform_submission->toArray();
      $webform_id = $webform_submission_array['webform_id'][0]['target_id'];

      $data = [
        'webform_id' => $webform_id,
        'sid' => $webform_submission->id(),
        'values' => $webform_submission->getData(),
      ];

      // Send data to addContact method
      $this->addContact($data);
    }
  }

}
