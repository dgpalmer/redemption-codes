<?php

namespace Drupal\redemption_codes\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Redemption Code entity.
 *
 * @ingroup redemption_codes
 *
 * @ContentEntityType(
 *   id = "redemption_code",
 *   label = @Translation("Redemption Code"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\redemption_codes\RedemptionCodeListBuilder",
 *     "views_data" = "Drupal\redemption_codes\Entity\RedemptionCodeViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\redemption_codes\Form\RedemptionCodeForm",
 *       "add" = "Drupal\redemption_codes\Form\RedemptionCodeForm",
 *       "edit" = "Drupal\redemption_codes\Form\RedemptionCodeForm",
 *       "delete" = "Drupal\redemption_codes\Form\RedemptionCodeDeleteForm",
 *     },
 *     "access" = "Drupal\redemption_codes\RedemptionCodeAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\redemption_codes\RedemptionCodeHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "redemption_code",
 *   admin_permission = "administer redemption codes",
 *   entity_keys = {
 *     "id" = "id",
 *     "code" = "redemption_code",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/redemption_code/{redemption_code}",
 *     "add-form" = "/admin/structure/redemption_code/add",
 *     "edit-form" = "/admin/structure/redemption_code/{redemption_code}/edit",
 *     "delete-form" = "/admin/structure/redemption_code/{redemption_code}/delete",
 *     "collection" = "/admin/structure/redemption_code",
 *   },
 *   field_ui_base_route = "redemption_code.settings",
 *   constraints = {
 *    "RedemptionCodeValue" = {}
 *   }
 * )
 */
class RedemptionCode extends ContentEntityBase implements RedemptionCodeInterface {

  use EntityChangedTrait;

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
  public function getCode() {
    return $this->get('code')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCode($code) {
    $this->set('code', $code);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRedemptionActions() {
    return $this->get('redemption_actions');
  }

  /**
   * {@inheritdoc}
   */
  public function setRedemptionActions($redemption_actions) {
    $this->set('redemption_actions', $redemption_actions);
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
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Code entity.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
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

    $fields['code'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Code'))
      ->setDescription(t('The Unique Code of the Code entity.'))
      ->setRequired(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // ## Redeemed By
    // @see https://www.drupal.org/docs/8/api/entity-validation-api/entity-validation-api-overview
    // @see https://www.drupal.org/docs/8/api/entity-validation-api/providing-a-custom-validation-constraint

    $fields['redemption_actions'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Redemption Actions'))
      ->setDescription(t('The associated redemption action.'))
      ->setRequired(FALSE)
      ->setSetting('target_type', 'action')
      ->setSetting('handler', 'default')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
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
      ]);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Code is published.'))
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
