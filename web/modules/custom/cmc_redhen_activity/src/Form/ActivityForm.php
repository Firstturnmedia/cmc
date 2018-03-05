<?php

namespace Drupal\cmc_redhen_activity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Activity edit forms.
 *
 * @ingroup cmc_redhen_activity
 */
class ActivityForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\cmc_redhen_activity\Entity\Activity */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = &$this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Activity.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Activity.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.cmc_redhen_activity.canonical', ['cmc_redhen_activity' => $entity->id()]);
  }

}
