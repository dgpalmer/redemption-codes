<?php

namespace Drupal\Tests\redemption_codes\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\redemption_codes\Traits\RedemptionCodesTestConfigTrait;
use Drupal\File\Entity\File;

class RedemptionCodeKernelTestBase extends KernelTestBase {

  use RedemptionCodesTestConfigTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['system', 'syslog', 'user', 'field', 'migrate', 'migrate_plus', 'migrate_source_csv', 'redemption_codes'];

  public $extraModules = ['file'];

  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    echo 'hello';
  }


  public function testSomething() {
    echo 'testing';
    $this->assertEquals(1, 1);
  }

  public function tearDown() {
  }

  protected function installConfig($modules) {
    foreach ((array) $modules as $module) {
      if (!$this->container->get('module_handler')->moduleExists($module)) {
        continue;
      }
      $this->container->get('config.installer')->installDefaultConfig('module', $module);
    }
  }

}
