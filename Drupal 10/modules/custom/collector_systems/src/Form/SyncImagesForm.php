<?php
namespace Drupal\collector_systems\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\collector_systems\ImagesSyncManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SyncImagesForm extends FormBase {


  protected $imagesSyncManager;


  /**
   * CreateTablesForm constructor.
   */
  public function __construct(ImagesSyncManager $imagesSyncManager) {
    $this->imagesSyncManager = $imagesSyncManager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('collector_systems.images_sync_manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cs_sync_images_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $request = \Drupal::request();
    $get_save_option = $request->query->get('save_option', default: '');
    $selected_option = '';
    if($get_save_option == 'database'){
      $selected_option = 'save_to_database';
    }elseif($get_save_option == 'directory'){
      $selected_option = 'save_to_directory';
    }

    // Wrapper container for horizontal layout
    $form['horizontal_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['form-horizontal-wrapper']],
    ];

    $form['horizontal_wrapper']['save_option'] = [
      '#type' => 'select',
      '#title' => $this->t('Save Images To'),
      '#options' => [
        'save_to_directory' => $this->t('Directory'),
        'save_to_database' => $this->t('Database'),
      ],
      '#default_value' => $selected_option,
      '#required' => TRUE,

    ];
    // Add a button to trigger the batch.
    $form['horizontal_wrapper']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Sync Images'),
      '#attributes' => ['class' => ['btn-dark']],
    ];

    // Add a container to display the status message.
    $form['status_message'] = [
      '#type' => 'markup',
      '#markup' => '<div id="batch-status"></div>',
    ];

    return $form;
  }



  

  /**
   * Start the batch process.
   */
  protected function startBatchProcess($data, $selected_option) {

    $operations = [];
    foreach ($data as $item) {
        if($item['image_type'] == 'object_images'){
            $operations[] = [[$this, 'processItem_ObjectImages'], [$item, $selected_option]];
        } else if($item['image_type'] == 'other_images'){
            $operations[] = [[$this, 'processItem_OtherImages'], [$item, $selected_option]];
        }
    }

    // Define the batch.
    $batch = [
      'title' => $this->t('Importing Images...'),
      'progress_message' => $this->t('Processed @current out of @total.'),
      'error_message' => $this->t('An error occurred during the download.'),
      'operations' => $operations,
      'finished' => [$this, 'batchFinished'],
    ];

    // Set and process the batch.
    batch_set($batch);
    // batch_process();
  }

  /**
   * Batch operation to process each item.
   */
  public function processItem_ObjectImages($item, $selected_option) {

   $this->imagesSyncManager->processItem_ObjectImages($item, $selected_option);
  }


  /**
   * Batch operation to process each item.
   */
  public function processItem_OtherImages($item, $selected_option) {

    $this->imagesSyncManager->processItem_OtherImages($item, $selected_option);
  }

  /**
   * Batch finished callback.
   */
  public function batchFinished($success, $results, $operations) {
    // store the sync started info in databse.
    collector_systems_update_CSSynced_table('images', 'manual', false,  true);

    if ($success) {
      \Drupal::messenger()->addMessage($this->t('Collector Systems - Images sync successfully completed.'));

      $redirect_url = Url::fromRoute('custom_api_integration.dashboard')->toString();
      return new RedirectResponse($redirect_url);
    }
    else {
      \Drupal::messenger()->addError($this->t('Collector Systems - An error occurred during images sync.'));
    }
    
  }

   /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // store the sync started info in databse.
    collector_systems_update_CSSynced_table('images', 'manual', true,  false);

    $selected_option = $form_state->getValue('save_option');
    $data_object_images = $this->imagesSyncManager->getDataForProcessingObjectImages();
    $data_other_images = $this->imagesSyncManager->getDataForProcessingOtherImages();
    $data = array_merge($data_object_images, $data_other_images);
    $this->startBatchProcess($data, $selected_option);
  }
 
}
