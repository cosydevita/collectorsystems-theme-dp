<?php

namespace Drupal\customize_object_detail_fields\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class CustomizeObjectDetailFieldsSettingsForm extends ConfigFormBase {

  public function getFormId() {
    return 'customize_object_detail_fields_settings_form';
  }

  protected function getEditableConfigNames() {
    return ['customize_object_detail_fields.settings'];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    // Load the current configuration settings.
      $config = \Drupal::config('customize_object_detail_fields.settings');

        // First Multiple Select List
        $form['select_field1'] = array(
          '#type' => 'select',
          '#title' => t('Available fields'),
          '#multiple' => TRUE,
          '#options' => $this->get_select_field1_options(),
          '#default_value' => $config->get('select_field1'),
          '#prefix' => '<div class="form-fields-wrapper">',
          '#attributes' => [
            'id' => 'select-field1',
          ],
        );
        // print_r(  $config->get('select_field1'));


        // Add a hidden field to store the current state of select_field1.
        $form['current_select_field1_options'] = [
          '#type' => 'hidden',
          '#default_value' => '',
          '#attributes' => [
            'id' => 'current-select-field1-options',
          ],
        ];


        $form['custom_html'] = array(
          '#markup' => '<div class="move-buttons">
            <a class="button" id="move-to-select2">Add +</a>
            <a class="button" id="move-to-select1">Remove -</a>
          </div>',
        );


        // Second Multiple Select List
        $form['select_field2'] = array(
          '#type' => 'select',
          '#title' => t('Selected field for Display'),
          '#multiple' => TRUE,
          '#options' => $this->get_select_field2_options(),
          '#prefix' => '<div id="select-field2-wrapper">',
          '#suffix' => '</div>',
          '#default_value' => $config->get('select_field2'),
          '#attributes' => [
            'id' => 'select-field2',
          ],
        );
          // Add a hidden field to store the current state of select_field1.
        $form['current_select_field2_options'] = [
          '#type' => 'hidden',
          '#default_value' => $config->get('select_field2'),
          '#attributes' => [
            'id' => 'current-select-field2-options',
          ],
          '#suffix' => '</div>',

        ];

        // // Submit Button
        // $form['submit'] = array(
        //   '#type' => 'submit',
        //   '#value' => t('Submit'),
        // );



    return parent::buildForm($form, $form_state);
  }

  public function get_select_field1_options(){
     // Load the current configuration settings.
     $config = \Drupal::config('customize_object_detail_fields.settings');

     if($config->get('select_field1')){
       $options =  $config->get('select_field1');


     }
     else{
       // Example usage:
       $fieldInfoService = \Drupal::service('customize_object_detail_fields.field_info_service');
       $fields_info = $fieldInfoService->getDisplayedFields('objects');
       $options['title'] = 'Title';
       foreach($fields_info as $field){
        $options[$field['machine_name']] = $field['label'];
       }
     }
     return $options;

  }

  public function get_select_field2_options(){
    // Load the current configuration settings.
    $config = \Drupal::config('customize_object_detail_fields.settings');

    if($config->get('select_field2')){
      $options =  $config->get('select_field2');

    }

    return $options;

 }

    public function submitForm(array &$form, FormStateInterface $form_state) {

      $current_field_1_options = json_decode($form_state->getValue('current_select_field1_options'), true);
      $current_field_2_options = json_decode($form_state->getValue('current_select_field2_options'), true);

      $this->config('customize_object_detail_fields.settings')
        ->set('select_field1',  $current_field_1_options)
        ->save();

      $this->config('customize_object_detail_fields.settings')
      ->set('select_field2',  $current_field_2_options)
      ->save();

      parent::submitForm($form, $form_state);
    }


}
