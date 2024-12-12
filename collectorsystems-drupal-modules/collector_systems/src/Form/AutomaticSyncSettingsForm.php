<?php

namespace Drupal\collector_systems\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\time_field\Time;


/**
 * Provides a form to manage automatic sync settings.
 */
class AutomaticSyncSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'collector_systems_automatic_sync_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = \Drupal::config('collector_systems.settings');

    $form['collector_systems_automatic_sync'] = [
      '#type' => 'select',
      '#title' => $this->t('Automatic Sync Frequency'),
      '#options' => [
        'manually' => $this->t('Manually'),
        'every-night' => $this->t('Every Night'),
        '7-days' => $this->t('7 Days'),
        '14-days' => $this->t('14 Days'),
        '30-days' => $this->t('30 Days'),
        '90-days' => $this->t('90 Days'),
        'annually' => $this->t('Annually'),
      ],
      '#default_value' => $config->get('collector_systems_automatic_sync') ?? 'manually',
    ];
    $form['collector_systems_automatic_sync_time'] = [
      '#type' => 'datetime',
      '#date_date_element' => 'none',
      '#date_time_element' => 'time',
      '#title' => 'Automatic Sync Time',
      '#default_value' => $config->get(key: 'collector_systems_automatic_sync_time') ? new DrupalDateTime($config->get(key: 'collector_systems_automatic_sync_time')) : NULL,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
      '#attributes' => [
        'class' => ['btn btn-dark'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $collector_systems_automatic_sync_time = $form_state->getValue('collector_systems_automatic_sync_time');
    if ($collector_systems_automatic_sync_time instanceof DrupalDateTime) {
      $collector_systems_automatic_sync_time = $collector_systems_automatic_sync_time->format('Y-m-d\TH:i:s');
    }
    // Save the configuration settings.
    \Drupal::configFactory()->getEditable('collector_systems.settings')
      ->set('collector_systems_automatic_sync', $values['collector_systems_automatic_sync'])
      ->set('collector_systems_automatic_sync_time', $collector_systems_automatic_sync_time)

      ->save();

    // Set a message to indicate successful saving.
    \Drupal::messenger()->addMessage($this->t('Automatic sync settings saved.'));
  }
}