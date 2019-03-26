<?php

namespace Drupal\redemption_codes\Form\Traits;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\appreciation_engine\AppreciationEngine;
use Drupal\appreciation_engine\Form\AEUserRegisterForm;
use AppreciationEngine\Utility as AEUtility;

trait RegistrationExtraFieldsFormTrait {

  /**
   * Update existing form elements to respect the required extra_fields settings.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  protected function prepareExtraFields(array &$form, FormStateInterface $form_state) {
    $form['#validate'][] = '::validateExtraFields';
  }

  /**
   * Check if the authenticating user needs to supply required extra fields.
   *
   * @param FormStateInterface $form_state
   * @return bool
   */
  protected function needsExtraFields(FormStateInterface $form_state) {
    return $form_state->get('needs_required_fields') ?: FALSE;
  }

  /**
   * Build the 2nd page of the form with additional required fields.
   *
   * @param array              $form
   * @param FormStateInterface $form_state
   * @return array
   */
  protected function buildExtraFieldsForm(array &$form, FormStateInterface $form_state) {
    $form =  $form_state->get('extra_fields');

    return $form;
  }

  /**
   * Attach ajax callbacks to form submit buttons.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  function attachAjaxExtraFields(array &$form, FormStateInterface $form_state) {
    $use_ajax = $this->config('appreciation_engine.forms')->get('use_ajax') ?: FALSE;
    if ($use_ajax) {
      // Make the form submit via AJAX
      $form['#prefix'] = "<div id=\"form-{$this->getFormIdCss()}--wrapper\">";
      $form['#suffix'] = '</div>';
      if (isset($form['actions']['submit'])) {
        $form['actions']['submit']['#ajax'] = [
          'callback' => '::ajaxCallback',
          'wrapper' => "form-{$this->getFormIdCss()}--wrapper",
        ];
      }
      if (isset($form['actions']['continue'])) {
        $form['actions']['continue']['#ajax'] = [
          'callback' => '::ajaxCallback',
          'wrapper' => "form-{$this->getFormIdCss()}--wrapper",
        ];
      }
    }
  }

  /**
   * Form validation callback to determine of there are remaining required
   * fields for this AE member.
   *
   * @param array              $form
   * @param FormStateInterface $form_state
   */
  public function validateExtraFields(array &$form, FormStateInterface $form_state) {
    $NEEDS_REQUIRED_FIELDS = FALSE;
    $extra_fields = parent::form($form, $form_state);
    $values = $form_state->getValues();

    $missing_fields = [];

    $errors = [];
    foreach ($extra_fields as $field_name => $settings) {
      if (is_array($settings)) {
        $isRequired = isset($settings['required']) ? $settings['required'] : FALSE;
        // Note if the field hasn't already been collected.
        $missing_fields[$field_name] = $settings;
        $NEEDS_REQUIRED_FIELDS = TRUE;
        if ($isRequired) {
          // If the field must be rollected then we need to interrupt login / register process and prompt for the field.
          $NEEDS_REQUIRED_FIELDS = TRUE;
          $required_message = isset($settings['required_message']) ? $settings['required_message'] : NULL;
          if (isset($form['#ae_field_mapping'][$field_name])) {
            // If the field is already defined as mapped then use it,
            $element_array = $form['#ae_field_mapping'][$field_name];

            $element = NestedArray::getValue($form, $element_array) ?: [];
            $field_title = $this->t(isset($element['#title']) ? $element['#title'] : end($map));
            $errors[$field_name] = [
              'field' => $element_array,
              'message' => $required_message ?: "The $field_title field is required."
            ];
          } else {
            // otherwise we're going to need a new field.
            $errors[$field_name] = ['message' => "The $field_name field is required."];
            // TODO limit_validation_errors ?
            if (isset($form[$field_name])) {
              $form_state->setErrorByName($field_name, $errors[$field_name]['message']);
            }
          }
        }
      }
    }

    if (!empty($values['pass'])) {
      $NEEDS_REQUIRED_FIELDS = FALSE;
    }
    $form_state->set('needs_required_fields', $NEEDS_REQUIRED_FIELDS);
    if ($NEEDS_REQUIRED_FIELDS) {
      $form_state->set('extra_fields', $missing_fields);
    }
  }

  /**
   * Store values in form_state for next page of form.
   *
   * @param FormStateInterface $form_state
   */
  public function preserveFormValues(FormStateInterface $form_state) {
    $form_state->cleanValues();
    $values = array_filter($form_state->getValues(), function($v) use ($form_state) {
      return !in_array($v, ['ae_user']);
    });
    $form_state->set('form_values', $values);
  }

  /**
   * Copy values from previous page back into form_state values from storage.
   *
   * @param FormStateInterface $form_state
   */
  public function restoreFormValues(FormStateInterface $form_state) {
    if ($page_values = $form_state->get('form_values')) {
      $form_state->setValues(array_merge($page_values, $form_state->getValues()));
    }
  }

}
