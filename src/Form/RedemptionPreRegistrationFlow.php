<?php

namespace Drupal\redemption_codes\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\PrependCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\user\RegisterForm;
use Drupal\user\Entity\User;
use Drupal\redemption_codes\Form\Traits\RedemptionRegistrationFlowTrait;
use Drupal\redemption_codes\Form\Traits\RegistrationExtraFieldsFormTrait;
use Drupal\appreciation_engine\Form\AEFormInterface;
use Drupal\appreciation_engine\Entity\AEUser;
use Drupal\appreciation_engine\AppreciationEngine;
use Drupal\appreciation_engine\Form\AEUserRegisterForm;
use Drupal\appreciation_engine\Member;
use Drupal\redemption_codes\Entity\Redemption;
use Drupal\redemption_codes\Entity\RedemptionInterface;
use Drupal\redemption_codes\Entity\RedemptionCode;
use AppreciationEngine\API;

/**
 * Class RedemptionPreRegistrationFlow.
 *
 * @package Drupal\redemption_codes\Form
 */
class RedemptionPreRegistrationFlow extends AEUserRegisterForm {
  use RedemptionRegistrationFlowTrait;
  use RegistrationExtraFieldsFormTrait;

  public function __construct(EntityManagerInterface $entity_manager, LanguageManagerInterface $language_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL, RendererInterface $renderer,  API $api = NULL){
    parent::__construct($entity_manager, $language_manager, $entity_type_bundle_info, $time, $renderer);
    $this->renderer = $renderer;
    $this->api = $api;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $config = Drupal::config('appreciation_engine.credentials');
    $api = NULL;
    try {
      $api = isset($config) ? new API(AppreciationEngine::getApiKey(), ['domain' => $config->get('domain')]) : NULL;
    } catch (\Exception $e) {
      \Drupal::logger('appreciation_engine')->error($e->getMessage());
    }
    return new static(
      $container->get('entity.manager'),
      $container->get('language_manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('renderer'),
      $api
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'redemption_pre_registration_flow';
  }

  /**
   * {@inheritdoc}
   */
  function getBaseFormId(){
    return 'ae_user_register_form';
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state)
  {
    $form = parent::form($form, $form_state);
    $this->attachRedemptionToRegistrationForm($form, $form_state);

    $this->prepareExtraFields($form, $form_state);
    if (self::needsExtraFields($form_state)) {
      $form = self::buildExtraFieldsForm($form, $form_state);
      $form['#attributes']['class'][] = 'user-register-form';
    } else {
      if (!isset($form['redemption_code'])) {
        $form['redemption_code'] = array(
          '#type' => 'textfield',
          '#title' => 'Redemption Code',
          '#size' => 25,
          '#description' => $this->t('Enter your redemption code.'),
          '#required' => TRUE,
        );
      }
      foreach ($form as $key => $settings) {
        if ($key != 'redemption_code') {
          unset($form[$key]);
        }
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actionsElement(array $form, FormStateInterface $form_state) {
    $element = parent::actionsElement($form, $form_state);

    if (!self::needsExtraFields($form_state)) {
      $element['submit']['#value'] = $this->t('Redeem Code & Register Account');
      $element['submit']['#validate'] = ['::validateForm', '::validateExtraFields'];
      $element['submit']['#submit'] = ['::submitExtraFields'];
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $this->attachAjaxExtraFields($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  function processForm($element, FormStateInterface $form_state, $form) {
    parent::processForm($element, $form_state, $form);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    self::restoreFormValues($form_state);

    if (self::needsExtraFields($form_state)) {
      $account = parent::validateForm($form, $form_state);
    } else {
      $redemption_code = $form_state->getValue('redemption_code');
      if (!empty($redemption_code)) {

        // Load User 1 temporarily to create the redemption
        $user = User::load(1);

        // Create
        $_redemption = [
          'redemption_code' => $form_state->getValue('redemption_code'),
          'user_id' => $user,
        ];
        $redemption = Redemption::create($_redemption);

        $violations = $redemption->validate();

        if ($violations->count() > 0) {
          $message = $violations[0]->getMessage();
          $form_state->setErrorByName('redemption_code', $message);
        } else {
          $form_state->set('redemption', $redemption);
        }

      }
    }
  }

  /**
   * Do basic validation of the EntityForm.
   * @param array              $form
   * @param FormStateInterface $form_state
   */
  public function validateEntity(array &$form, FormStateInterface $form_state) {
    self::restoreFormValues($form_state);
    parent::validateForm($form, $form_state);
  }

  /**
   * Step one submit handler of multistep login + extra_fields form.
   *
   * @param array              $form
   * @param FormStateInterface $form_state
   */
  public function submitExtraFields(array &$form, FormStateInterface $form_state) {
    if (!self::needsExtraFields($form_state)) {
      self::submitForm($form, $form_state);
      self::save($form, $form_state);
    } else {
      self::preserveFormValues($form_state);
      $form_state->setRebuild(TRUE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var RedemptionInterface $redemptionInterface */
    $redemption = $form_state->get('redemption');
    $password = trim($form_state->getValue('pass'));
    parent::submitForm($form, $form_state);
    $account = $this->getEntity();
    $form_state->set('redemption', $redemption);
  }

  /**
   * Save the registered User entity.
   * @see Drupal\user\RegisterForm::save()
   *
   * @param array              $form
   * @param FormStateInterface $form_state
   * @return int|void
   */
  public function save(array $form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $member = $form_state->get('ae_user');
    if ($member) {
      $account = $this->entity;
      $pass = $account->getPassword();
      // Save has no return value so this cannot be tested.
      // Assume save has gone through correctly.
      $account->save();
      $form_state->set('user', $account);
      $form_state->setValue('uid', $account->id());

      $this->logger('user')->notice('New user: %name %email.', array('%name' => $form_state->getValue('name'), '%email' => '<' . $form_state->getValue('mail') . '>', 'type' => $account->link($this->t('Edit'), 'edit-form')));

      // Add plain text password into user account to generate mail tokens.
      $account->password = $pass;

      $AEUser = AEUser::createFrom($member, $account);
      $AEUser->save(TRUE);
      if ($account->isActive()) {
        _user_mail_notify('register_no_approval_required', $account);
        user_login_finalize($account);

        /** @var RedemptionInterface $redemptionInterface */
        $redemption = $form_state->get('redemption');
        if (!empty($redemption)) {
          $redemption->setOwner($account);
          $redemption->save(TRUE);
          drupal_set_message('Your account has been created and you have redeemed your code.');
          $redemptions_config = \Drupal::config('redemption_codes.settings')->get('redemptions');

          // Get the url from path.
          $redirect = \Drupal::service('path.validator')->getUrlIfValid($redemptions_config['redirect_path']);
          $form_state->setRedirectUrl($redirect);
        }

      } else {
        AppreciationEngine::sendVerificationEmail($member, $account);
        drupal_set_message('Your account has been created and you have redeemed your code.');
      }


    } else {
      $this->setErrorByName($form, $form_state,'ae_user', 'An error occurred registering your account, please try again in a few minutes.');
    }

  }

}
