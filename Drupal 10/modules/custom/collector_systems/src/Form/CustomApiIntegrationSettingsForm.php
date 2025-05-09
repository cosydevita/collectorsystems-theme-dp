<?php

namespace Drupal\collector_systems\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\collector_systems\Csconstants;

class CustomApiIntegrationSettingsForm extends ConfigFormBase {

  public function getFormId() {
    return 'custom_api_integration_settings_form';
  }

  protected function getEditableConfigNames() {
    return ['collector_systems.settings'];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('collector_systems.settings');

    $form['api_integrations'] = [
      '#type' => 'details',
      '#title' => $this->t('Collector Systems API Integration'),
      '#open' => TRUE,
    ];

    $form['api_integrations']['subscription_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subscription Key'),
      '#default_value' => $config->get('subscription_key'),
      '#required' => true,
    ];

    $form['api_integrations']['account_guid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Account GUID'),
      '#default_value' => $config->get('account_guid'),
      '#required' => true,
    ];

    $form['api_integrations']['subscription_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subscription ID'),
      '#default_value' => $config->get('subscription_id'),
      '#required' => true,
    ];

    $form['api_integrations']['azure_map_subscription_key'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Azure Map Subscription Key'),
      '#default_value' => $config->get('azure_map_subscription_key'),
      '#description' => $this->t('Enter subscription key for the Azure Map Integration'),
      '#rows' => 2,
    ];

    $form['ui_customizations'] = [
      '#type' => 'details',
      '#title' => $this->t('Collector Systems UI Customizations'),
      '#open' => TRUE,
    ];

    $form['ui_customizations']['show_field_labels'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show Field Labels on Detail'),
      '#default_value' => $config->get('show_field_labels'),
    ];

    $form['ui_customizations']['show_images_on_list_pages'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show Images on List Pages'),
      '#default_value' => $config->get('show_images_on_list_pages'),
    ];

    $form['ui_customizations']['enable_maps'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Maps'),
      '#default_value' => $config->get('enable_maps'),
    ];

    $form['ui_customizations']['enable_transition'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Transition effect on page load'),
      '#default_value' => $config->get('enable_transition'),
    ];

    $form['ui_customizations']['center_align_images'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Center align images on list pages'),
      '#default_value' => $config->get('center_align_images'),
    ];

    $form['ui_customizations']['image_bg_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Background color for images'),
      '#default_value' => $config->get('image_bg_color') ?? '#ffffff',
      '#description' => $this->t('Enter a hex color code or CSS color name.'),
    ];

    $form['ui_customizations']['bold_customized_field_labels'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Make Customized field labels more bold'),
      '#default_value' => $config->get('bold_customized_field_labels'),
    ];

    $form['ui_customizations']['underline_all_hyperlinks'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Underline all hyperlinks'),
      '#default_value' => $config->get('underline_all_hyperlinks'),
    ];

    $form['ui_customizations']['enable_zoom'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Zoom for Images'),
      '#default_value' => $config->get('enable_zoom'),
    ];

    $form['ui_customizations']['filter_keywords'] = [
      '#type' => 'select',
      '#title' => $this->t('Filter Images By Keywords'),
      // '#options' => [
      //   'Armchairs' => $this->t('Armchairs'),
      //   'art gallery' => $this->t('Art Gallery'),
      //   'Canvas' => $this->t('Canvas'),
      //   'clock' => $this->t('Clock'),
      //   'collection' => $this->t('Collection'),
      //   'fine arts' => $this->t('Fine Arts'),
      //   'gallery' => $this->t('Gallery'),
      //   'necklace' => $this->t('Necklace'),
      //   'oil painting' => $this->t('Oil Painting'),
      //   'paint' => $this->t('Paint'),
      //   'sculpture' => $this->t('Sculpture'),
      //   'wall art' => $this->t('Wall Art'),
      //   'wall painting' => $this->t('Wall Painting'),
      // ],
      '#options' => $this->get_filter_keywords_options() ?? [],
      '#default_value' => $config->get('filter_keywords'),
      '#multiple' => true,
      '#attributes' => [
        'class' => ['filter-keywords-select'],
      ],
    ];

    $form['ui_customizations']['items_per_page'] = [
      '#type' => 'select',
      '#title' => $this->t('Items Per Page'),
      '#options' => [
        '9' => '9',
        '15' => '15',
        '18' => '18',
        '21' => '21',
        '24' => '24',
        '27' => '27',
        '30' => '30',
        '39' => '39',
        '51' => '51',
        '75' => '75',
        '105' => '105',
        '198' => '198',
      ],
      '#default_value' => $config->get('items_per_page'),
    ];

    $active_theme =  \Drupal::config('system.theme')->get('default');
    if($active_theme == 'collectorsystems'){
      $form['homepage_image'] = [
        '#type' => 'managed_file',
        '#title' => $this->t(string: 'Home Page Image'),
        '#default_value' => $config->get('homepage_image'),
        '#upload_location' => 'public://images/homepage', // Adjust the upload location as needed
        '#upload_validators' => [
          'file_validate_extensions' => ['png gif jpg jpeg'],
        ],
      ];
    }

    $form['#attached']['library'][] = 'collector_systems/select2';
    $form['#attached']['library'][] = 'collector_systems/custom_api_integration_settings_form';


    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('collector_systems.settings')
      ->set('subscription_key', $form_state->getValue('subscription_key'))
      ->set('account_guid', $form_state->getValue('account_guid'))
      ->set('subscription_id', $form_state->getValue('subscription_id'))
      ->set('azure_map_subscription_key', $form_state->getValue('azure_map_subscription_key'))
      ->set('show_field_labels', $form_state->getValue('show_field_labels'))
      ->set('show_images_on_list_pages', $form_state->getValue('show_images_on_list_pages'))
      ->set('enable_maps', $form_state->getValue('enable_maps'))
      ->set('enable_transition', $form_state->getValue('enable_transition'))
      ->set('center_align_images', $form_state->getValue('center_align_images'))
      ->set('image_bg_color', $form_state->getValue('image_bg_color'))
      ->set('bold_customized_field_labels', $form_state->getValue('bold_customized_field_labels'))
      ->set('underline_all_hyperlinks', $form_state->getValue('underline_all_hyperlinks'))
      ->set('enable_zoom', $form_state->getValue('enable_zoom'))
      ->set('filter_keywords', $form_state->getValue('filter_keywords'))
      ->set('items_per_page', $form_state->getValue('items_per_page'))
      ->set('homepage_image', $form_state->getValue('homepage_image'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  public function get_filter_keywords_options(){

    $config = \Drupal::config('collector_systems.settings');
    $subsKey = $config->get('subscription_key');
    $subAcntId = $config->get('account_guid');
    $subsId = $config->get('subscription_id');


    $url = csconstants::Public_API_URL.$subAcntId.'/AttachmentKeywords?$apply=groupby((AttachmentKeywordString))';
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
    "Accept: application/json",
    "Ocp-Apim-Subscription-Key:$subsKey ",
    "Cache-Control:no-cache",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $Detaildata = curl_exec($curl);
    curl_close($curl);

    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if($httpcode == 403)
    {
        // get_template_part( 403 );
        exit();
    }

    $Detaildata = json_decode($Detaildata, TRUE);
    $keywords = [];
    if(isset($Detaildata['value'])){
      foreach($Detaildata['value'] as $data)
      {
        if(isset($data['AttachmentKeywordString'])){
          $keywords[] = $data['AttachmentKeywordString'];
        }
      }
      $options = [];

      foreach($keywords as $keyword){
        $options[$keyword] = $this->t($keyword);

      }
      return $options;

    }


  }

}
