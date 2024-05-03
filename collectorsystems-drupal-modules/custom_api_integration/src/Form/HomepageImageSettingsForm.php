<?php

namespace Drupal\custom_api_integration\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class HomepageImageSettingsForm extends ConfigFormBase {

  public function getFormId() {
    return 'homepage_image_settings_form';
  }

  protected function getEditableConfigNames() {
    return ['homepage_image.settings'];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('homepage_image.settings');
    $form['homepage_image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Hoamepage Image'),
      '#default_value' => $config->get('homepage_image'),
      '#upload_location' => 'public://images/homepage', // Adjust the upload location as needed
      '#upload_validators' => [
        'file_validate_extensions' => ['png gif jpg jpeg'],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('homepage_image.settings')
      ->set('homepage_image', $form_state->getValue('homepage_image'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
