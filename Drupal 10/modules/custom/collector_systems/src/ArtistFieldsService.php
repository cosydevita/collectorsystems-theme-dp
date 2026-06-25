<?php
namespace Drupal\collector_systems;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Get fields from api.
 * used in Artist Details field customization
 * used in Artist List field customization
 */
class ArtistFieldsService {

  protected $configFactory;

  public function __construct(ConfigFactoryInterface $configFactory)
  {
      $this->configFactory = $configFactory;
  }

  public static function create(ContainerInterface $container)
  {
      return new static(
          $container->get('config.factory')
      );
  }


/**
 * Get artist list fields
 * 
 * @return array List of artist fields
 * 
 */
  public function getArtistFields() {
    
    // static for now. Will make dynamic based on API response in future.
    $fields = [
      [
        'FieldName' => 'ArtistName',
        'FieldValue' => 'Artist Name'
      ],
      [
        'FieldName' => 'ArtistFirst',
        'FieldValue' => 'Artist First Name'
      ],
      [
        'FieldName' => 'ArtistLast',
        'FieldValue' => 'Artist Last Name'
      ],
      [
        'FieldName' => 'ArtistCompany',
        'FieldValue' => 'Artist Company'
      ],
      [
        'FieldName' => 'ArtistAliasFirst',
        'FieldValue' => 'Artist Alias First'
      ],
      [
        'FieldName' => 'ArtistAliasLast',
        'FieldValue' => 'Artist Alias Last'
      ],
      [
        'FieldName' => 'ArtistYears',
        'FieldValue' => 'Artist Years'
      ],
      [
        'FieldName' => 'ArtistNationality',
        'FieldValue' => 'Artist Nationality'
      ],
      [
        'FieldName' => 'ArtistLocale',
        'FieldValue' => 'Artist Locale'
      ],
      [
        'FieldName' => 'ArtistGender',
        'FieldValue' => 'Artist Gender'
      ],
      [
        'FieldName' => 'ArtistSchool',
        'FieldValue' => 'Artist School'
      ],
      [
        'FieldName' => 'ArtistRace',
        'FieldValue' => 'Artist Race'
      ],
      [
        'FieldName' => 'ArtistEthnicity',
        'FieldValue' => 'Artist Ethnicity'
      ],
      [
        'FieldName' => 'ArtistLink',
        'FieldValue' => 'Artist Link'
      ],
      [
        'FieldName' => 'ArtistBio',
        'FieldValue' => 'Artist Bio'
      ],
      [
        'FieldName' => 'ArtistMemo',
        'FieldValue' => 'Artist Memo'
      ],
    ];

    return  $fields;

  }

  /**
   * Get the value of ObjectField label from the database
   *
   * @param string $field_name
   *
   * @return string
   *  Label string
   */
  public function getObjectFieldLabelFromDatabase($field_name){

    $db = \Drupal::database();
    $table = "collector_systems_clsobjects_fields";

    $query = $db->select($table, 'c')
    ->fields('c', ['fieldvalue'])
    ->condition('fieldname', $field_name)
    ->condition('fieldtype', 'ObjectDetail');

    $field_value = $query->execute()->fetchField();
    if ($field_value === FALSE) {
      // No value found
      $field_value = $field_name;
    }

    return $field_value;

  }
}

