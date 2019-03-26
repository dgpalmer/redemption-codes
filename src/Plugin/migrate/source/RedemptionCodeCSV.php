<?php

namespace Drupal\redemption_codes\Plugin\migrate\source;

use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\migrate_source_csv\Plugin\migrate\source\CSV;
use Drupal\system\Entity\Action;

/**
 * Source for CSV.
 *
 * If the CSV file contains non-ASCII characters, make sure it includes a
 * UTF BOM (Byte Order Marker) so they are interpreted correctly.
 *
 * @MigrateSource(
 *   id = "redemptioncodecsv"
 * )
 */
class RedemptionCodeCSV extends CSV {

  /**
   * List of available source fields.
   *
   * Keys are the field machine names as used in field mappings, values are
   * descriptions.
   *
   * @var array
   */
  protected $fields = [];

  /**
   * List of key fields, as indexes.
   *
   * @var array
   */
  protected $keys = [];

  /**
   * The file class to read the file.
   *
   * @var string
   */
  protected $fileClass = '';

  /**
   * The file object that reads the CSV file.
   *
   * @var \SplFileObject
   */
  protected $file = NULL;

  /**
   * Redemption Actions configured for this migration.
   *
   * @var array
   */
  protected $redemption_actions = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);

    // Path is required.
    if (empty($this->configuration['path'])) {
      throw new MigrateException('You must declare the "path" to the source CSV file in your source settings.');
    }

    // Key field(s) are required.
    if (empty($this->configuration['keys'])) {
      throw new MigrateException('You must declare "keys" as a unique array of fields in your source settings.');
    }

    $this->fileClass = empty($configuration['file_class']) ? 'Drupal\migrate_source_csv\CSVFileObject' : $configuration['file_class'];

    // Load the Redemption Actions configured for the migration
    $config = \Drupal::config('redemption_codes.csv');
    $redemption_actions = $config->get('redemption_actions');
    foreach($redemption_actions as $redemption_action) {
      $this->redemption_actions[] = Action::load($redemption_action);
    }
  }

  /**
   * Return a string representing the source file path.
   *
   * @return string
   *   The file path.
   */
  public function __toString() {
    return $this->configuration['path'];
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    // File handler using header-rows-respecting extension of SPLFileObject.
    return parent::initializeIterator();
  }

  /**
   * {@inheritdoc}
   */
  public function getIDs() {
    $ids = [];
    foreach ($this->configuration['keys'] as $delta => $value) {
      if (is_array($value)) {
        $ids[$delta] = $value;
      }
      else {
        $ids[$value]['type'] = 'string';
      }
    }
    return $ids;
  }

  public function prepareRow(Row $row)
  {
    $row->setDestinationProperty('redemption_actions', $this->redemption_actions);
    return parent::prepareRow($row); // TODO: Change the autogenerated stub
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [];
    foreach ($this->getIterator()->getColumnNames() as $column) {
      $fields[key($column)] = reset($column);
    }

    // Any caller-specified fields with the same names as extracted fields will
    // override them; any others will be added.
    if (!empty($this->configuration['fields'])) {
      $fields = $this->configuration['fields'] + $fields;
    }

    return $fields;
  }

}
