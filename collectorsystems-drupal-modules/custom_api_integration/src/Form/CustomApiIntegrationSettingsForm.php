<?php

namespace Drupal\custom_api_integration\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\custom_api_integration\Csconstants;
use Drupal\Core\Database\Database;
use Drupal\Core\StreamWrapper\PublicStream;

class CustomApiIntegrationSettingsForm extends ConfigFormBase {

  public function getFormId() {
    return 'custom_api_integration_settings_form';
  }

  protected function getEditableConfigNames() {
    return ['custom_api_integration.settings'];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('custom_api_integration.settings');

    $form['subscription_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subscription Key'),
      '#default_value' => $config->get('subscription_key'),
      '#required' => true,
    ];

    $form['account_guid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Account GUID'),
      '#default_value' => $config->get('account_guid'),
      '#required' => true,
    ];

    $form['subscription_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subscription ID'),
      '#default_value' => $config->get('subscription_id'),
      '#required' => true,
    ];

    $form['show_field_labels'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show Field Labels on Detail'),
      '#default_value' => $config->get('show_field_labels'),
    ];

    $form['enable_zoom'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Zoom for Images'),
      '#default_value' => $config->get('enable_zoom'),
    ];

    $form['filter_keywords'] = [
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

    $form['items_per_page'] = [
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

    $form['footer_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Footer Text'),
      '#default_value' => $config->get('footer_text'),
    ];

    $form['#attached']['library'][] = 'custom_api_integration/select2';
    $form['#attached']['library'][] = 'custom_api_integration/custom_api_integration_settings_form';


    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('custom_api_integration.settings')
      ->set('subscription_key', $form_state->getValue('subscription_key'))
      ->set('account_guid', $form_state->getValue('account_guid'))
      ->set('subscription_id', $form_state->getValue('subscription_id'))
      ->set('show_field_labels', $form_state->getValue('show_field_labels'))
      ->set('enable_zoom', $form_state->getValue('enable_zoom'))
      ->set('filter_keywords', $form_state->getValue('filter_keywords'))
      ->set('items_per_page', $form_state->getValue('items_per_page'))
      ->set('footer_text', $form_state->getValue('footer_text'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  public function get_filter_keywords_options(){

    $config = \Drupal::config('custom_api_integration.settings');
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
