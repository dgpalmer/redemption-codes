<?php

namespace Drupal\redemption_codes\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class CodeSettingsForm.
 *
 * @ingroup redemption_codes
 */
class RedemptionCodeSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'redemption_codes.settings',
    ];
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'redemption_code_settings';
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    $values = $form_state->getValues();
    $config = $this->config('redemption_codes.settings');


    $redemptions = [
      'redirect_path' => $values['redirect_path'],
      'default_path' => $values['default_path'],
    ];
    $registration_flow_config = $config->get('registration_flow');
    $registration_flow = [
      'path' => $values['registration_flow_path'],
      'enabled' => $values['registration_flow_enabled'],
      'block' => $values['registration_flow_block']
    ];
    $pre_registration_flow_config = $config->get('pre_registration_flow');
    $pre_registration_flow = [
      'path' => $values['pre_registration_flow_path'],
      'enabled' => $values['pre_registration_flow_enabled'],
      'block' => $values['pre_registration_flow_block']
    ];
    if (array_diff($registration_flow, $registration_flow_config)) {
      $this->updateFlows($registration_flow);
    }
    if (array_diff($pre_registration_flow, $pre_registration_flow_config)) {
      $this->updateFlows($registration_flow);
    }
    $this->config('redemption_codes.settings')
      ->set('redemptions', $redemptions)
      ->set('registration_flow', $registration_flow)
      ->set('pre_registration_flow', $pre_registration_flow)
      ->save();

  }

  protected function updateFlows($flow) {
  }

  /**
   * Defines the settings form for Code entities.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Form definition array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Load the modules existing configuration
    $config = $this->config('redemption_codes.settings');

    /**
     * Redemption Settings
     */
    $redemptions = $config->get('redemptions');

    // Get route name from path.
    $url_object = \Drupal::service('path.validator')->getUrlIfValid($redemptions['default_path']);

    $form['redemption_settings']['redemptions'] = [
      '#type' => 'fieldset',
      '#title' => 'Redemption Settings',
    ];
    // Redemption Redirect Path
    $form['redemption_settings']['redemptions']['redirect_path'] = [
      '#title' => $this->t('Successful Redemption Redirect Path'),
      '#type' => 'textfield',
      '#default_value' => $redemptions['redirect_path'],
    ];
    // Redemption Redirect Path
    $form['redemption_settings']['redemptions']['default_path'] = [
      '#title' => $this->t('Default Redemption Path'),
      '#type' => 'textfield',
      '#default_value' => $redemptions['default_path'],
    ];

    /**
     * Registration Flow
     */
    $registration_flow = $config->get('registration_flow');
    $form['redemption_settings']['registration_flow'] = [
      '#type' => 'fieldset',
      '#title' => 'Registration Flow',
    ];
    // Enabled Registration Flow
    $form['redemption_settings']['registration_flow']['registration_flow_enabled'] = [
      '#title' => $this->t('Enabled'),
      '#default_value' => $registration_flow['enabled'],
      '#type' => 'checkbox',
    ];
    // Enabled Registration Flow Block
    $form['redemption_settings']['registration_flow']['registration_flow_block'] = [
      '#title' => $this->t('Create Block'),
      '#default_value' => $registration_flow['block'],
      '#type' => 'checkbox',
    ];
    // Registration Flow Path
    $form['redemption_settings']['registration_flow']['registration_flow_path'] = [
      '#title' => $this->t('Form Path'),
      '#default_value' => $registration_flow['path'],
      '#type' => 'textfield',
    ];

    /**
     * Pre Registration Flow
     */
    $pre_registration_flow = $config->get('pre_registration_flow');
    $form['redemption_settings']['pre_registration_flow'] = [
      '#type' => 'fieldset',
      '#title' => 'Pre Registration Flow',
    ];
    // Enabled Pre Registration Flow
    $form['redemption_settings']['pre_registration_flow']['pre_registration_flow_enabled'] = [
      '#title' => $this->t('Enabled'),
      '#default_value' => $pre_registration_flow['enabled'],
      '#type' => 'checkbox',
    ];
    // Enabled Pre Registration Flow Block
    $form['redemption_settings']['pre_registration_flow']['pre_registration_flow_block'] = [
      '#title' => $this->t('Create Block'),
      '#default_value' => $pre_registration_flow['block'],
      '#type' => 'checkbox',
    ];
    // Pre Registration Flow Path
    $form['redemption_settings']['pre_registration_flow']['pre_registration_flow_path'] = [
      '#title' => $this->t('Form Path'),
      '#default_value' => $pre_registration_flow['path'],
      '#type' => 'textfield',
    ];

    // Redemption Code Settings
    $form['code_settings'] = [
      '#type' => 'fieldset',
      '#title' => 'Redemption Code Settings',
    ];
    $form['code_settings']['allow_duplicates'] = [
      '#title' => $this->t('Allow The Same Code String to be used more than once'),
      '#default_value' => $config->get('allow_duplicates'),
      '#type' => 'checkbox',
    ];
    $form['actions'] = [
      '#type' => 'actions'
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit')
    ];
    return $form;
  }

}
