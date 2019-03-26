<?php

namespace Drupal\redemption_codes\Form\Traits;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\Html;

trait RedemptionRegistrationFlowTrait
{

  /**
   * Add the necessary AE field and data attribute.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  function attachRedemptionToRegistrationForm(array &$form, FormStateInterface $form_state)
  {
    $form['#attributes']['data-drupal-redemption-attach'] = TRUE;
    // Create a form field to hold AE user json data.
    $form['redemption'] = [
      '#type' => 'hidden',
      '#value' => NULL,
    ];

  }
}
