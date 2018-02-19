<?php

namespace Drupal\test_webform_handler\Plugin\WebFormHandler;

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

/**
 * Send a Webform submission to a queue.
 *
 * @WebformHandler(
 *   id = "test_webform_handler_test",
 *   label = @Translation("Test Webform Handler Test"),
 *   category = @Translation("Test Webform Handler"),
 *   description = @Translation("Test Webform Handler"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class TestWebformHandler extends WebformHandlerBase {
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
  public function addToQueue(array $webform_data) {
    $queue_name = 'test_webform_handler';

    try {
      // Create a queue
      $queue = $this->queueFactory->get($queue_name);

      // Create a queue item
      $queue->createItem($webform_data);
    }
    // @todo fix exception catching
    catch (EntityStorageException $e) {
      watchdog_exception('webform.queue', $e);
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

      $queue_data = [
        'webform_id' => $webform_id,
        'sid' => $webform_submission->id(),
        'values' => $webform_submission->getData(),
      ];

      // Send data to queue
      $this->addToQueue($queue_data);
    }
  }

}
