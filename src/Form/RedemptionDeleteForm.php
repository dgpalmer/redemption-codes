<?php

namespace Drupal\redemption_codes\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for deleting Redemption entities.
 *
 * @ingroup redemption_codes
 */
class RedemptionDeleteForm extends ContentEntityDeleteForm {
  /**
   * Returns the question to ask the user.
   *
   * @return string
   *   The form question. The page title will be set to this value.
   */
  public function getQuestion()
  {
    return $this->t('Are you sure you want to delete this redemption?');
  }
  /**
   * Returns the route to go to if the user cancels the action.
   *
   * @return \Drupal\Core\Url
   *   A URL object.
   */
  public function getCancelUrl()
  {
    return new Url('entity.redemption.collection');
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();
    drupal_set_message($this->t('Redemption has been deleted.'));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
