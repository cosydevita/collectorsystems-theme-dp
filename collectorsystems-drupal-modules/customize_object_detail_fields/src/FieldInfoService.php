<?php
namespace Drupal\customize_object_detail_fields;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;



/**
 * Get field information for fields displayed in "Manage Fields" for a specific content type.
 */
class FieldInfoService {

  protected $entityFieldManager;
  protected $entityTypeBundleInfo;

  public function __construct(EntityFieldManagerInterface $entityFieldManager, EntityTypeBundleInfoInterface $entityTypeBundleInfo) {
    $this->entityFieldManager = $entityFieldManager;
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_field.manager'),
      $container->get('entity_type.bundle.info')
    );
  }

  public function getDisplayedFields($content_type) {
    $fields_info = [];


    // Load the Entity Field Manager service.
    $entityFieldManager = \Drupal::service('entity_field.manager');

    // Replace 'objects' with the machine name of your content type.
    $content_type_machine_name = $content_type;

    // Get the field definitions for the content type.
    $field_definitions = $entityFieldManager->getFieldDefinitions('node', $content_type_machine_name);

    foreach ($field_definitions as $field_name => $field_definition) {
      if ($field_definition instanceof FieldConfig) {
        $field_machine_name = $field_definition->getName();
        $field_label = $field_definition->getLabel();

        $fields_info[] = [
          'machine_name' => $field_machine_name,
          'label' => $field_label,
        ];
      }
    }

    return $fields_info;
  }
}

