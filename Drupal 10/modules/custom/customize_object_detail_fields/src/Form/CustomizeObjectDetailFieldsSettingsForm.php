<?php

namespace Drupal\customize_object_detail_fields\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;


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
          '#default_value' => $config->get('select_field1'),
          '#attributes' => [
            'id' => 'current-select-field1-options',
          ],
        ];


        $form['custom_html'] = array(
          '#markup' => '<div class="move-buttons">
            <a class="button" id="move-to-select2">ADD <i class="fa-solid fa-right-long"></i></a>
            <a class="button" id="move-to-select1">Remove <i class="fa-solid fa-left-long"></i></a>
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
        $form['sort_buttons'] = array(
          '#markup' => '<div class="sort-buttons">
            <a class="button" id="move-up"><i class="fa-solid fa-chevron-up"></i></a>
            <a class="button" id="move-down"><i class="fa-solid fa-chevron-down"></i></a>
          </div>',
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

    $selected_options = $this->get_select_field2_options();

    //get all the options
    $ObjectFieldsService = \Drupal::service('customize_object_detail_fields.object_fields_service');
    $ObjectFields = $ObjectFieldsService->getObjectFields();

    foreach($ObjectFields as $field){
      // "FieldName": "AboriginalName",
      // "FieldValue": "Aboriginal Name"
      $field_name = $field['FieldName'];
      $field_value = $field['FieldValue'];

      if(!(isset($selected_options[$field_name]))){
        $options[$field_name] = $field_value;
      }
    }


    return $options;

  }

  public function get_select_field2_options(){

    $db = \Drupal::database();

    $tblnm = "clsobjects_fields";
    $settblnm = $tblnm;

    $query = $db->select($settblnm, 'c')
      ->fields('c', ['fieldname', 'fieldvalue'])
      ->condition('fieldtype', 'ObjectDetail');

    $result = $query->execute()->fetchAllKeyed();

    return $result;

  }

    public function submitForm(array &$form, FormStateInterface $form_state) {

      $current_field_2_options = json_decode($form_state->getValue('current_select_field2_options'), true);

      $table_name = 'clsobjects_fields';

      // Delete rows where fieldtype is 'ObjectDetail'.
      \Drupal::database()->delete($table_name)
        ->condition('fieldtype', 'ObjectDetail')
        ->execute();

      // Get selected items from the form.
      $chkfieldarray = $current_field_2_options;

      // Check if there are selected items.
      if (!empty($chkfieldarray)) {
        foreach ($chkfieldarray as $FieldName => $value) {
          // Assuming $ch is already defined with data.

          // Insert into the database.
          \Drupal::database()->insert($table_name)
            ->fields(array(
              'fieldname' => $FieldName,
              'fieldvalue' => $value,
              'fieldtype' => 'ObjectDetail',
            ))
            ->execute();
        }
      }
      parent::submitForm($form, $form_state);
    }


}
