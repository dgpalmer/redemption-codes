<?php

namespace Drupal\redemption_codes\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Redemption entity.
 *
 * @ingroup redemption_codes
 *
 * @ContentEntityType(
 *   id = "redemption",
 *   label = @Translation("Redemption"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\redemption_codes\RedemptionListBuilder",
 *     "views_data" = "Drupal\redemption_codes\Entity\RedemptionViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\redemption_codes\Form\RedemptionForm",
 *       "add" = "Drupal\redemption_codes\Form\RedemptionForm",
 *       "delete" = "Drupal\redemption_codes\Form\RedemptionDeleteForm",
 *     },
 *     "access" = "Drupal\redemption_codes\RedemptionAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\redemption_codes\RedemptionHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "redemption",
 *   fieldable = FALSE,
 *   admin_permission = "administer redemption codes",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/redemption/{redemption}",
 *     "add-form" = "/admin/structure/redemption/add",
 *     "delete-form" = "/admin/structure/redemption/{redemption}/delete",
 *     "collection" = "/admin/structure/redemption",
 *   },
 *   field_ui_base_route = "redemption.settings"
 * )
 */
class Redemption extends ContentEntityBase implements RedemptionInterface {

  protected $redemption_code_id = NULL;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getRedemptionCode() {
    return $this->get('redemption_code')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRedemptionCode($redemption_code) {
    $this->set('redemption_code', $redemption_code);
    return $this;
  }


  /**
   * {@inheritdoc}
   */
  public function getRedemptionCodeId() {
    return $this->get('redemption_code_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setRedemptionCodeId($redemption_code_id) {
    $this->set('redemption_code_id', $redemption_code_id);
    return $this;
  }


  /**
   * {@inheritdoc}
   */
  public function _getRedemptionCodeId() {
    return $this->redemption_code_id;
  }

  /**
   * {@inheritdoc}
   */
  public function _setRedemptionCodeId($redemption_code_id) {
    $this->redemption_code_id = $redemption_code_id;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Redeemed by'))
      ->setDescription(t('The User whom redeemed the code.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['redemption_code_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Redeemed Code ID'))
      ->setDescription(t('The Redemption Code ID that was redeemed.'))
      ->setSetting('target_type', 'redemption_code')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'type' => 'hidden',
      ])
      ->setDisplayOptions('form', [
        'type' => 'hidden',
      ]);

    $fields['redemption_code'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Redemption Code'))
      ->setDescription(t('Enter your Redemption Code.'))
      ->setRequired(TRUE)
      ->setDefaultValue('')
      ->addConstraint('RedemptionEligibility')
      ->setDisplayOptions('view', [
        'type' => 'hidden',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'settings' => array('display_label' => 'hidden', 'label' => 'hidden'),
        'weight' => -2,
      ]);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Redemption is published.'))
      ->setDefaultValue(TRUE);

    $fields['redeemed_on'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Redeemed On'))
      ->setDescription(t('The time that the redemption occurred.'));


    return $fields;
  }
}
