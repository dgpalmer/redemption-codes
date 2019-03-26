<?php

namespace Drupal\Tests\redemption_codes\Traits;

use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Site\Settings;
use Drupal\Component\Utility\NestedArray;
use Drupal\user\Entity\User;

/**
 * Class RedemptionCodesTestConfigTrait
 * Test utility methods to intialize the redemption_codes module for testing.
 * @package Drupal\redemption_codes\Tests
 */
trait RedemptionCodesTestConfigTrait {

  protected $fanclub_members;
  protected $redemption_codes;


  protected function initRedemptionCodesApplication() {

  }

  /**
   * In order to use a default module configuration we are
   * loading all configs defined in the test site's settings.php
   * $config_directories$config_directories$config_directories['testing'].
   */
  protected function importTestConfiguration() {
    try {
      $config_test_dir = \config_get_config_directory('testing');
      if ($config_test_dir_storage = new FileStorage($config_test_dir)) {
        $this->mergeConfig($config_test_dir_storage, $this->container->get('config.storage'));
      }
    } catch(\Exception $e) {
      $this->markTestSkipped("Error importing testing configuration. Error: \n". $e->getMessage() ."\n Trace: \n" . $e->getTraceAsString());
    }
  }

  /**
   * Load the settings in the test settings.php, preserving the mock settings
   * from KernelTestBase::setUpFilesystem().
   */
  protected function prepareTestSettings() {
    global $config_directories;
    $_config_directories = $config_directories;
    // Load the test site settings
    $_settings = Settings::getInstance() ? Settings::getAll() : [];
    if ($test_url = getenv('SIMPLETEST_BASE_URL')) {
      // Check for a settings.php for the test domain.
      $test_url = parse_url($test_url);
      $test_host = $test_url['host'];
      $this->host = $test_host;
      $root = isset($this->root) ? $this->root : \Drupal::root();
      Settings::initialize($root, "sites/$test_host", $this->classLoader);
      $settings = Settings::getInstance() ? Settings::getAll() : [];
      // restore the old settings & $config_directories
      $config_directories[CONFIG_SYNC_DIRECTORY] = $_config_directories[CONFIG_SYNC_DIRECTORY];
      new Settings(array_merge($settings, $_settings));
    }

    // Create admin role
    $role = \Drupal\user\Entity\Role::create(['id' => 'administrator', 'name' => 'Administrator']);
    $role->save();
  }

  protected function initTestConfigs() {

    $test = $this->config('redemption_codes.test');
    $this->fanclub_members = $test->get('users');
    if (empty($this->fanclub_members)) {
      $this->markTestSkipped('Missing test fan club members.');
    }
  }

  protected function createFanClubMembers() {
    foreach ($this->fanclub_members as $fanclub_member) {
      // ## Create user.
      $user = User::create([
        'name' => $fanclub_member['name'],
        'mail' => $fanclub_member['mail'],
        'password' => $fanclub_member['password'],
        'status' => 1,
      ]);
      $user->save();
    }
  }

  protected function resetAll() {
    // Clear all database and static caches and rebuild data structures.
    drupal_flush_all_caches();
    $this->container = \Drupal::getContainer();
  }

  protected function getFanClubMembers($index) {
    return $this->fanclub_members[$index];
  }

  /**
   * @see Drupal\Tests\ConfigTestTrait
   *
   * @param StorageInterface $source_storage
   * @param StorageInterface $target_storage
   */
  protected function mergeConfig(StorageInterface $source_storage, StorageInterface $target_storage) {
    foreach ($source_storage->listAll() as $name) {
      $config = $target_storage->read($name) ?: [];
      $merge = $source_storage->read($name) ?: [];
      $target_storage->write($name, NestedArray::mergeDeep($config, $merge));
    }
  }

  protected function perform_redemption($index) {
    $fanclub_members = $this->fanclub_members[$index];
    $redemption = $this->api->redeem([
      'redemption_code' => $fanclub_members['redemption_code'],
      'user_id' => $fanclub_members['user_id'],
    ]);
    return $redemption;
  }
}
