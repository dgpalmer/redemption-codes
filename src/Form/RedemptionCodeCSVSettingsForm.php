<?php

namespace Drupal\redemption_codes\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\migrate_plus\Entity\Migration;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Drupal\system\Entity\Action;
use Drupal\file\Entity\File;

/**
 * Class CodeCSVSettingsForm.
 *
 * @ingroup redemption_codes
 */
class RedemptionCodeCSVSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'redemption_codes.csv',
    ];
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'redemption_code_csv';
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file = current($form_state->getValue('file'));
    $_redemption_actions = [];
    $redemption_actions = $form_state->getValue(['redemption_actions_fieldset', 'redemption_actions']);
    foreach($redemption_actions as $redemption_action) {
      $_redemption_actions[] = $redemption_action['redemption_action'];
    }
    $this->config('redemption_codes.csv')
      ->set('file', $file)
      ->set('redemption_actions', $_redemption_actions)
      ->save();

    $this->updateCSVMigration($file);

    if ($form_state->getValue('import')) {
      $this->executeCSVMigration();
    }

    drupal_set_message('Your Redemption Codes have been imported.');
    $form_state->setRedirect('entity.redemption_code.collection');

  }

  /**
   * Updat the Migration Configuration to use the new csv file
   *
   * @param $fid
   */
  protected function updateCSVMigration($fid) {
    // Grab the full path  to the csv file
    $file = \Drupal\file\Entity\File::load($fid);
    $uri = $file->getFileUri();
    $stream_wrapper_manager = \Drupal::service('stream_wrapper_manager')->getViaUri($uri);
    $path = $stream_wrapper_manager->realpath();

    // Update the Migration Configuration with the new path
    $migration = Migration::load('redemption_codes_csv');
    $source = $migration->get('source');
    $source['path'] = $path;
    $migration->set('source', $source);
    $migration->enable();
    $migration->setStatus(MigrationInterface::STATUS_IDLE);
    $migration->save();
  }

  /**
   * Execute the Migration
   */
  protected function executeCSVMigration() {
    $migration = \Drupal::service('plugin.manager.migration')->createInstance('redemption_codes_csv');
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $status = $executable->import();
    drupal_set_message('Your Redemption Codes have been imported.');
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
    $config = $this->config('redemption_codes.csv');
    $form['#tree'] = TRUE;
    $form_state->setCached(FALSE);

    // CSV File

    $fid = $config->get('file');
    $file = null;
    $form['file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Redemption Codes CSV File'),
      '#description' => $this->t('Expecting a CSV File of Redemption Codes from Magento or Shopify.'),
      '#upload_location' => 'public://redemption_codes/csv/',
      '#upload_validators' => [
        'file_validate_extensions' => ['csv']
      ],
      '#states' => [
        'visible' => [
          ':input[name="file_type"]' => ['value' => $this->t('Upload')]
        ]
      ]
    ];
    if (!empty($fid)) {
      $form['file']['#default_value'] = [$fid];
    }

    $form['import'] = [
      '#type' => 'checkbox',
      '#title' => t('Import the Redemption Codes upon submission of this form.'),
      '#default_value' => TRUE,
    ];

    /**
     * Redemption Actions
     */
    $form['redemption_actions_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Redemption Actions'),
      '#attributes' => ['id' => 'redemption-actions-wrapper'],
    ];

    $redemption_actions_count = 1;
    if (is_integer($form_state->get('redemption_actions_count'))) {
      $redemption_actions_count = $form_state->get('redemption_actions_count');
    } 

    $_redemption_actions = $form_state->getValue(array('redemption_actions_fieldset', 'redemption_actions'));
    $redemption_actions = [];
    if (empty($_redemption_actions)) {
      $redemption_actions = $config->get('redemption_actions');
    } else {
      foreach($_redemption_actions as $redemption_action) {
        $redemption_actions[] = $redemption_action['redemption_action'];
      }
    }

    if (empty($redemption_actions_count)) {
      if (empty($redemption_actions)) {
        $redemption_actions_count = $form_state->set('redemption_actions_count', 1);
      } else {
        $redemption_actions_count = count($redemption_actions);
        $form_state->set('redemption_actions_count', $redemption_actions_count);
      }
    }
    for ($i = 0; $i < $redemption_actions_count; $i++) {
      $form['redemption_actions_fieldset']['redemption_actions'][$i] = [
        '#type' => 'fieldset',
      ];
      $form['redemption_actions_fieldset']['redemption_actions'][$i]['redemption_action'] = [
        '#type' => 'entity_autocomplete',
        '#title' => $this->t('Redemption Action'),
        '#description' => $this->t('Search by Action Name'),
        '#target_type' => 'action',
        '#maxlength'=> 255,
      ];
      if (!empty($redemption_actions[$i])) {
        $redemption_action = Action::load($redemption_actions[$i]);
        $form['redemption_actions_fieldset']['redemption_actions'][$i]['redemption_action']['#default_value'] = $redemption_action;
      }
      if ($redemption_actions_count != 1) {
        $form['redemption_actions_fieldset']['redemption_actions'][$i]["remove"] = [
          '#type' => 'submit',
          '#value' => t('Remove'),
          '#submit' => array('::remove'),
          '#ajax' => [
            'callback' => '::removeCallback',
            'wrapper' => 'redemption-actions-wrapper',
            'row' => $i
          ]
        ];
      }
      if ($i < $redemption_actions_count || $i == 0) {
        //$form['business_units'][$i]['client_id']['#required'] = TRUE;
      }
    }

    $form['redemption_actions_fieldset']['actions'] = [
      '#type' => 'actions',
      '#weight' => 50,
    ];
    $form['redemption_actions_fieldset']['actions']['add_redemption_action'] = [
      '#type' => 'submit',
      '#value' => t('Add one more'),
      '#submit' => array('::addOne'),
      '#ajax' => [
        'callback' => '::addMoreCallback',
        'wrapper' => 'redemption-actions-wrapper'
      ],
    ];
    $form['redemption_actions_fieldset']['actions']['remove_redemption_action'] = [
      '#type' => 'submit',
      '#value' => t('Remove One'),
      '#submit' => array('::removeOne'),
      '#ajax' => [
        'callback' => '::addMoreCallback',
        'wrapper' => 'redemption-actions-wrapper'
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit')
    ];
    return $form;
  }


  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addMoreCallback(array &$form, FormStateInterface $form_state) {
    $redemption_actions_count = $form_state->get('redemption_actions_count');
    return $form['redemption_actions_fieldset'];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    $redemption_actions_count = $form_state->get('redemption_actions_count');
    // subtract by 1 to account for the array indices
    $redemption_actions_count--;
    $redemption_actions_entered = $form_state->getValue(array('redemption_actions_fieldset', 'redemption_actions'));
    if (!empty($redemption_actions_entered[$redemption_actions_count])) {
      // add 2 to account for the array indices (an extra for the one we removed earlier)
      $add_button = $redemption_actions_count + 2;
      $form_state->set('redemption_actions_count', $add_button);
    }
    $form_state->setRebuild();
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function removeOne(array &$form, FormStateInterface $form_state) {
    $redemption_actions_count = $form_state->get('redemption_actions_count');
    // subtract by 1 to account for the array indices
    $redemption_actions_count--;
    $redemption_actions_entered = $form_state->getValue(array('redemption_actions_fieldset', 'redemption_actions'));
    if (!empty($redemption_actions_entered[$redemption_actions_count])) {
      // add 2 to account for the array indices (an extra for the one we removed earlier)
      $remove_button = $redemption_actions_count ;
      $form_state->set('redemption_actions_count', $remove_button);
    }
    $form_state->setRebuild();
  }

  public function removeCallback(array &$form, FormStateInterface $form_state)
  {
    $redemption_actions_count = $form_state->get('redemption_actions_count');
    $triggered = $form_state->getTriggeringElement();
    $row_to_remove = $triggered['#ajax']['row'];
    unset($form['redemption_actions_count']['redemption_actions'][$row_to_remove]);
    return $form['redemption_actions_count'];
  }

  /**
   * Submit handler for the "remove-one-more" button.
   *
   * Decrements the max counter and causes a rebuild.
   */
  public function remove(array &$form, FormStateInterface &$form_state)
  {
    $triggered = $form_state->getTriggeringElement();
    $row_to_remove = $triggered['#ajax']['row'];
    $_redemption_actions = $form_state->getValue(array('redemption_actions_fieldset', 'redemption_actions'));

    $redemption_actions = [];

    // remove the selected business unit from the array
    array_splice($_redemption_actions, $row_to_remove, 1);
    foreach($_redemption_actions as $redemption_action) {
      $redemption_actions[] = $redemption_action['redemption_action'];
    }

    $form_state->setValue(array('redemption_actions_fieldset', 'redemption_actions'), $redemption_actions);


    $redemption_actions_count = $form_state->get('redemption_actions_count');
    $form_state->set('redemption_actions_count', $redemption_actions_count);

    $form_state->setRebuild();
  }

  /**
   * Helper function to refresh the subscriptions lists
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function refreshLists(array &$form, FormStateInterface $form_state) {
//    $form_state->setRebuild();
  }
}
