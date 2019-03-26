<?php

namespace Drupal\Tests\redemption_codes\Functional;

use Drupal;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\redemption_codes\Traits\RedemptionCodesTestConfigTrait;


/**
 * @see Drupal\Tests\user\Functional\FunctionalHttpTestBase
 * @group redemption_codes
 */
class FunctionalRedemptionTestBase extends BrowserTestBase {

  use RedemptionCodesTestConfigTrait;

  protected $strictConfigSchema = FALSE;

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['system', 'action' , 'syslog', 'user', 'field', 'migrate', 'migrate_plus', 'migrate_source_csv', 'redemption_codes'];

  /*
   * The cookie jar.
   *
   * @var \GuzzleHttp\Cookie\CookieJar
   */
  protected $cookies;

  protected function setUp() {
    parent::setUp();
    $this->prepareTestSettings();
    $this->importTestConfiguration();
    $this->initTestConfigs();
    $this->createFanClubMembers();
  }

  protected function tearDown() {
    // TODO SET UP TEAR DOWN AND ITS HELPER FUNCTIONS
  }

}
