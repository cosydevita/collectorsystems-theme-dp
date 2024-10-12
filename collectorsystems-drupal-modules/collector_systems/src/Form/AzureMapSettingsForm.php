<?php

namespace Drupal\collector_systems\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a settings form for the Collector Systems Azure Map Integration
 */
class AzureMapSettingsForm extends FormBase {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Constructs a new AzureMapSettingsForm.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'collector_systems_azure_map_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $subscription_key = $this->state->get('collector_systems_azure_map.subscription_key');


    $form['subscription_key'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Subscription Key'),
      '#default_value' => $subscription_key,
      '#description' => $this->t('Enter subscription key for the Azure Map Integration'),
    ];


    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }



  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->state->set('collector_systems_azure_map.subscription_key', $form_state->getValue('subscription_key'));
    $this->messenger()->addMessage($this->t('The Azure Map settings has been updated.'));
  }

}
