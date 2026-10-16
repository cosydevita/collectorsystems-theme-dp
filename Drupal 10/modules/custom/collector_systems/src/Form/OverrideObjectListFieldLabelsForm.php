<?php

namespace Drupal\collector_systems\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Lets admins override the display label of fields selected for the Object
 * List page's "Sort By" dropdown, without altering each field's underlying
 * default label.
 */
class OverrideObjectListFieldLabelsForm extends ConfigFormBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $database;

  /**
   * Constructs the form.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(ConfigFactoryInterface $config_factory, Connection $database) {
    parent::__construct($config_factory);
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'override_object_list_field_labels_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['collector_systems.field_label_overrides'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $selected = $this->database->select('collector_systems_clsobjects_fields', 'c')
      ->fields('c', ['fieldname', 'fieldvalue'])
      ->condition('fieldtype', 'ObjectList')
      ->execute()
      ->fetchAllKeyed();

    if (empty($selected)) {
      $form['empty'] = [
        '#markup' => '<p>' . $this->t('No fields are currently selected. Choose fields in the Select Fields tab first.') . '</p>',
      ];
      return parent::buildForm($form, $form_state);
    }

    $overrides = $this->config('collector_systems.field_label_overrides')->get('object_list') ?? [];

    $form['description'] = [
      '#markup' => '<p>' . $this->t('Leave a field blank to use its default label. These labels are used in the "Sort By" dropdown on the Object List page.') . '</p>',
    ];

    $form['labels'] = [
      '#type' => 'table',
      '#header' => [$this->t('Field'), $this->t('Override Label')],
      '#tree' => TRUE,
    ];
    foreach ($selected as $fieldname => $default) {
      $form['labels'][$fieldname]['field'] = [
        '#plain_text' => $default,
      ];
      $form['labels'][$fieldname]['override'] = [
        '#type' => 'textfield',
        '#title' => $default,
        '#title_display' => 'invisible',
        '#default_value' => $overrides[$fieldname] ?? '',
        '#maxlength' => 150,
        '#attributes' => ['placeholder' => $default],
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('collector_systems.field_label_overrides');
    $existing = $config->get('object_list') ?? [];
    $submitted = $form_state->getValue('labels', []);

    foreach ($submitted as $fieldname => $row) {
      $label = trim($row['override'] ?? '');
      if ($label !== '') {
        $existing[$fieldname] = $label;
      }
      else {
        unset($existing[$fieldname]);
      }
    }

    $config->set('object_list', $existing)->save();
    parent::submitForm($form, $form_state);
  }

}
