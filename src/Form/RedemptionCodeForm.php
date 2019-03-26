<?php

namespace Drupal\redemption_codes\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Code edit forms.
 *
 * @ingroup redemption_codes
 */
class RedemptionCodeForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\redemption_codes\Entity\RedemptionCode */
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
        drupal_set_message($this->t('Created the %code Redemption Code.', [
          '%code' => $entity->code->value,
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %code Code.', [
          '%code' => $entity->code->value,
        ]));
    }
    $form_state->setRedirect('entity.redemption_code.collection');
  }

}
