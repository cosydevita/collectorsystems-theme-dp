<?php

namespace Drupal\collector_systems\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\File\FileSystemInterface;
use Drupal\collector_systems\DataSyncManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\collector_systems\CollectorSystemsGetApiData;
use Symfony\Component\HttpFoundation\RequestStack;

class CreateTablesForm extends FormBase
{
  protected $fileSystem;

  protected $dataSyncManager;

  protected $collectorSystemsGetApiData;

  protected $requestStack;


  /**
   * CreateTablesForm constructor.
   *
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The file system service.
   */
  public function __construct(
    FileSystemInterface $fileSystem, 
    DataSyncManager $dataSyncManager, 
    CollectorSystemsGetApiData $collectorSystemsGetApiData,
    RequestStack $request_stack
  ){
    $this->fileSystem = $fileSystem;
    $this->dataSyncManager = $dataSyncManager;
    $this->collectorSystemsGetApiData = $collectorSystemsGetApiData;
    $this->requestStack = $request_stack;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('file_system'),
      $container->get('collector_systems.data_sync_manager'),
      $container->get('collector_systems.collector_systemsts_get_api_data'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'cs_create_tables_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $request = $this->requestStack->getCurrentRequest();
    $btn_action = $request->query->get('btn_action', '');


    $form['btn_action'] = [
      '#type' => 'radios',
      '#title' => $this->t('Data'),
      '#options' => [
        'reset_and_create_dataset' => $this->t('Reset and Create Dataset'),
        'update_dataset' => $this->t('Update Dataset'),
      ],
      '#default_value' => $btn_action,
      '#required' => TRUE,

    ];
    // Add a button to trigger the batch.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
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
  public function startBatchProcess($data, $btn_action, $is_automatic_sync = null)
  {

    $operations = [];
    foreach ($data as $item) {
      $operations[] = [[$this, 'processItem'], [$item, $btn_action]];
    }

    // Define the batch.
    $batch = [
      'title' => $this->t('Importing Data from Collector Systems API...'),
      'progress_message' => $this->t('Processed @current out of @total.'),
      'error_message' => $this->t('An error occurred during the download.'),
      'operations' => $operations,
      'finished' => [$this, 'batchFinished'],
    ];

    \Drupal::logger('collector_systems')->debug('PHP_SAPI: '. PHP_SAPI);

    // If triggered by Cron, ensure batch processing works without UI interaction.
    if ($is_automatic_sync == TRUE) {
      $batch['progressive'] = FALSE; // Ensure batch runs non-interactively.

      \Drupal::logger('collector_systems')->debug('Operations:'. json_encode($operations));
      \Drupal::logger('collector_systems')->debug('Automatic sync Triggered');
      batch_set($batch);

      // Explicitly process the batch during cron.
      $batch =& batch_get();
      if (!empty($batch)) {
        $batch['progressive'] = FALSE; // Ensure non-interactive mode.
        batch_process();
      }
    } else {
      \Drupal::logger('collector_systems')->debug('Batch Process triggered for: ' . $btn_action);
      batch_set($batch);
    }

  }

  // public function startQueueProcess($data, $btn_action){
  //   // Add items to the queue.
  //   foreach ($data as $item) {
  //     $this->queue->createItem([
  //       'data' => $item,
  //       'btn_action' => $btn_action,
  //     ]);
  //   }
  // }

  /**
   * Batch operation to process each item.
   */
  public function processItem($item, $btn_action)
  {
    $this->dataSyncManager->processItem($item, $btn_action);
  }

  /**
   * Batch finished callback.
   */
  public function batchFinished($success, $results, $operations)
  {
    \Drupal::logger('collector_systems')->debug('Batch Finished.');

    if ($success) {
      \Drupal::logger('collector_systems')->debug('Batch success.');
      // store the sync completed info in databse.
      collector_systems_update_CSSynced_table('data', 'manual', false,  true);
      \Drupal::messenger()->addMessage($this->t('Collector Systems: Import completed successfully.'));

      $redirect_url = "/admin/collector-systems/api-dashboard";
      return new RedirectResponse($redirect_url);

    } else {
      \Drupal::logger('collector_systems')->debug('Batch was not successful.');
      \Drupal::messenger()->addError($this->t('Collector Systems: An error occurred during the import.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    

    $btn_action = $form_state->getValue('btn_action');

    //drop tables
    $this->dataSyncManager->clear_tables_data($btn_action);

    // create tables
    $this->dataSyncManager->custom_api_integration_create_tables($btn_action);

    // store the sync started info in databse.
    collector_systems_update_CSSynced_table('data', 'manual', true,  false);

    // start batch process.
    $data = $this->dataSyncManager->getDataForProcessing();
    $this->startBatchProcess($data, $btn_action);
  }

}
