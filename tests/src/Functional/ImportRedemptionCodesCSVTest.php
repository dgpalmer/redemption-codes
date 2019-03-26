<?php

namespace Drupal\Tests\redemption_codes\Kernel;

use Drupal;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\redemption_codes\Traits\RedemptionCodesTestConfigTrait;
use Drupal\migrate_plus\Entity\Migration;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_plus\Entity\MigrationInterface;
use Drupal\migrate\MigrateMessage;
use Drupal\redemption_codes\Entity\Redemption;
use Drupal\User\Entity\User;
use Drupal\system\Entity\Action;

/**
 * @see Drupal\Tests\user\Functional\LoginRegisterFormTest
 * @group redemption_codes
 */
class ImportRedemptionCodesCSVTest extends Drupal\Tests\redemption_codes\Functional\FunctionalRedemptionTestBase {

  protected function setUp()
  {

    parent::setUp();
    $module_handler = \Drupal::service('module_handler');
    $path = DRUPAL_ROOT . '/' . $module_handler->getModule('redemption_codes')->getPath() . '/artifacts/example_codes.csv';

    // Update the Migration Configuration with the new path
    $migration = Migration::load('redemption_codes_csv');
    $source = $migration->get('source');
    $source['path'] = $path;

    $migration->set('source', $source)
      ->save();
  }

  public function testRedemption() {
    // Import the Codes
    $migration = \Drupal::service('plugin.manager.migration')->createInstance('redemption_codes_csv');

    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $status = $executable->import();
    $this->assertEquals(1, $status);

    $redemptions = [];

    // Invalid
    $redemptions[] = [
      'user' => 3,
      'code' => 'Stan0011231231',
      'error' => 'invalid',
    ];

    // valid
    $redemptions[] = [
      'user' => 3,
      'code' => 'Stan001',
    ];

    // claimed
    $redemptions[] = [
      'user' => 4,
      'code' => 'Stan001',
      'error' => 'taken'
    ];

    //
    $redemptions[] = [
      'user' => 3,
      'code' => 'Stan001',
      'error' => 'ownerAlready'
    ];

    foreach ($redemptions as $redemption) {
      $this->validation($redemption);
    }
  }

  protected function tearDown()
  {
    parent::tearDown(); // TODO: Change the autogenerated stub
  }

  protected function validation($_redemption) {
    $user = User::load($_redemption['user']);
    $redemption = Redemption::create([
      'redemption_code' => $_redemption['code'],
      'user_id' => $user,
    ]);
    $violations = $redemption->validate();

    if ($violations->count() > 0) {
      $violation_data = $violations[0]->getMessageParameters();
      $this->assertEquals($_redemption['error'], $violation_data['error']);
    } else {
      $redemption->save();
    }
  }
}
