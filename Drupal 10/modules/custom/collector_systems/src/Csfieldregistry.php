<?php

namespace Drupal\collector_systems;

/**
 * Collector Systems field-behavior registry.
 *
 * Single source of truth for how an object field's value is derived and
 * whether/how it links to a related record. Consumed by src/Csfieldresolver.php.
 *
 * The UserDefined* families (UserDefined1-40, UserDefinedRichText1-18,
 * UserDefinedControlledVocab1-10, UserDefinedNumber/Currency/Date1-2) are
 * deliberately NOT listed here — they're numbered, homogeneous,
 * admin-configurable slots, so Csfieldresolver::getCategory() classifies them
 * by name prefix instead, so a new slot needs zero edits to this file.
 */
class Csfieldregistry {

  // Value-derivation categories.

  // General (non-UDF) date fields: date('m/d/Y', strtotime(...)).
  public const DATE_FIELDS = [
    Csconstants::InventoryDate,
    Csconstants::CatalogDate,
    Csconstants::CollectionDate,
    Csconstants::IdentifiedDate,
    Csconstants::SpeciesAuthorDate,
    Csconstants::SubspeciesAuthorDate,
    Csconstants::ManufactureDate,
    Csconstants::ReleaseDate,
    Csconstants::ProductionDate,
    Csconstants::ThreatenedEndangeredDate,
    Csconstants::CompletenessDate,
    Csconstants::MovementAuthorizationDate,
    Csconstants::LocationConditionDate,
  ];

  // Yes/No boolean fields.
  public const BOOLEAN_FIELDS = [
    Csconstants::CatalogFolder,
    Csconstants::ControlledProperty,
    Csconstants::ThreatenedEndangeredSpeciesSynonym,
    Csconstants::ThinSection,
  ];

  // Richtext fields (Xss::filterAdmin(), rendered in a "show more" span on the
  // object detail page).
  public const RICHTEXT_FIELDS = [
    Csconstants::DimensionMemo,
    Csconstants::InventoryMemo,
    Csconstants::ObjectDescription,
    Csconstants::Signatures,
    Csconstants::Inscriptions,
    Csconstants::Labels,
    Csconstants::Provenance,
    Csconstants::ReferenceNotes,
    Csconstants::ResearchNotes,
    Csconstants::StaffNotes,
    Csconstants::RelatedCollections,
    Csconstants::KeyDescriptor,
    Csconstants::WithinSiteProveniance,
    Csconstants::SubspeciesDescriptiveName,
    Csconstants::History,
    Csconstants::Transcription,
    Csconstants::CastAndCrew,
    Csconstants::Synopsis,
    Csconstants::Waterbody,
    Csconstants::AssociatedSpecies,
    Csconstants::Drainage,
    Csconstants::ObjectUse,
    Csconstants::StartingInstructions,
    Csconstants::RegistrationNotes,
    Csconstants::TitleStatusNotes,
    Csconstants::RepairsMade,
    Csconstants::CompletenessNote,
    Csconstants::MovementMemo,
    Csconstants::LocationAccessMemo,
    Csconstants::LocationConditionMemo,
    Csconstants::LocationSecurityMemo,
    Csconstants::ObjectNameNote,
    Csconstants::FieldCollectionMemo,
    Csconstants::HabitatMemo,
    Csconstants::StratigraphicUnitMemo,
  ];

  // JSON child-array fields: field => [childArrayKey, idProperty, nameProperty],
  // as consumed by CustomTwig::implodeChildArrayProperty(). Both entries use
  // ArtistId/ArtistName per the OData $expand=ArtistMaker($select=ArtistId,
  // ArtistName) shape (confirmed against CollectorSystemsGetApiData.php) —
  // AdditionalArtistMakers previously looked up ArtistMakerId/ArtistMakerName,
  // which don't exist in the decoded JSON, so it rendered blank everywhere.
  public const ADDITIONAL_ARRAY_FIELD_MAP = [
    'AdditionalArtists'      => [ 'Artist', 'ArtistId', 'ArtistName' ],
    'AdditionalArtistMakers' => [ 'ArtistMaker', 'ArtistId', 'ArtistName' ],
  ];

  // Fields with a pre-flattened "{field}Text" sibling column.
  public const ADDITIONAL_TEXT_COLUMN_FIELDS = [
    Csconstants::AdditionalCreators,
    Csconstants::AdditionalAuthors,
    Csconstants::AdditionalPublishers,
  ];

  // NOTE: ArtistMakerCompany/Locale/Nationality/School/Years/Gender/Race/
  // Ethnicity/Bio/AliasFirst/AliasLast need no category entry here.
  // DataSyncManager.php syncs them into their own
  // Maker-prefixed columns (mirroring the pre-existing ArtistMakerName
  // pattern), so they fall through to the 'generic' category like any other
  // plain field — no remapping needed at render time.

  // Link types — orthogonal to the categories above, consumed by both
  // fillObjectListHtml() and GetCustomizedObjectDetailsForTheme().

  // Fields that link to the current object's own detail page.
  public const SELF_LINK_FIELDS = [
    Csconstants::Title,
    Csconstants::InventoryNumber,
  ];

  // Fields that open the related collection, keyed off the row's CollectionId.
  public const COLLECTION_LINK_FIELDS = [
    Csconstants::FullCollectionName,
    Csconstants::CollectionName,
  ];

  // Fields that link to the artist-detail page, keyed off the row's ArtistId.
  public const ARTIST_LINK_FIELDS = [
    Csconstants::ArtistName,
    Csconstants::ArtistFirst,
    Csconstants::ArtistLast,
  ];

  // Fields that link to the artist-detail page, keyed off the row's ArtistMakerId.
  public const ARTIST_MAKER_LINK_FIELDS = [
    Csconstants::ArtistMakerName,
    Csconstants::ArtistMakerFirst,
    Csconstants::ArtistMakerLast,
  ];

  // Sync-direction only (consumed by DataSyncManager::resolveFieldValue()):
  // field => path segments to walk against the raw API row, e.g.
  // Csconstants::Color => ['Color', 'Term'] means $value['Color']['Term'].
  // Extracted field-for-field from DataSyncManager's former 404-case switch
  // via a PHP-tokenizer-based script cross-checked against the source, not
  // hand-transcribed — this is the bulk of what that switch did, since
  // almost every case was exactly one of: a plain passthrough identical to
  // its own default: branch (needs no entry here), a date/boolean/JSON-array
  // field (already covered by the categories above), or a single-object
  // nested lookup (this map).
  public const SYNC_NESTED_LOOKUP_MAP = [
    Csconstants::AboriginalName => ['AboriginalName', 'Term'],
    Csconstants::AdditionalArea => ['AdditionalArea', 'Term'],
    Csconstants::AgeStage => ['AgeStage', 'Term'],
    Csconstants::ArtDirectorContactName => ['ArtDirectorContact', 'ContactName'],
    Csconstants::ArtistMakerAliasFirst => ['ArtistMaker', 'ArtistAliasFirst'],
    Csconstants::ArtistMakerAliasLast => ['ArtistMaker', 'ArtistAliasLast'],
    Csconstants::ArtistMakerBio => ['ArtistMaker', 'ArtistBio'],
    Csconstants::ArtistMakerCompany => ['ArtistMaker', 'ArtistCompany'],
    Csconstants::ArtistMakerEthnicity => ['ArtistMaker', 'ArtistEthnicity'],
    Csconstants::ArtistMakerFirst => ['ArtistMaker', 'ArtistFirst'],
    Csconstants::ArtistMakerGender => ['ArtistMaker', 'ArtistGender'],
    Csconstants::ArtistMakerLast => ['ArtistMaker', 'ArtistLast'],
    Csconstants::ArtistMakerLocale => ['ArtistMaker', 'ArtistLocale'],
    Csconstants::ArtistMakerName => ['ArtistMaker', 'ArtistName'],
    Csconstants::ArtistMakerNationality => ['ArtistMaker', 'ArtistNationality'],
    Csconstants::ArtistMakerRace => ['ArtistMaker', 'ArtistRace'],
    Csconstants::ArtistMakerSchool => ['ArtistMaker', 'ArtistSchool'],
    Csconstants::ArtistMakerYears => ['ArtistMaker', 'ArtistYears'],
    // ArtistName: fixes a missing else in the original case — it could
    // leave the DB column entirely unset instead of '' when Artist existed
    // but Artist.ArtistName didn't.
    Csconstants::ArtistName => ['Artist', 'ArtistName'],
    Csconstants::AuthorName => ['Author', 'AuthorName'],
    Csconstants::BroadClassOfMaterial => ['BroadClassOfMaterial', 'Term'],
    Csconstants::CSClass => ['Class'],
    Csconstants::CatalogLevel => ['CatalogLevel', 'Term'],
    Csconstants::CatalogerContactName => ['CatalogerContact', 'ContactName'],
    Csconstants::CinematographerContactName => ['CinematographerContact', 'ContactName'],
    // CollectionName/FullCollectionName: the original *live* case (a
    // duplicate label existed with a stricter guard, but PHP always took
    // the first, weaker one) only checked `!empty($value['Collection'])`
    // before reading the child key, which could write a raw PHP null (with
    // an undefined-index warning) instead of '' if Collection existed
    // without a CollectionName. The uniform path-walker below normalizes
    // this to '' like every other nested field, consistent with the
    // ArtistName fix above.
    Csconstants::CollectionName => ['Collection', 'CollectionName'],
    Csconstants::CollectorContactName => ['CollectorContact', 'ContactName'],
    Csconstants::Color => ['Color', 'Term'],
    Csconstants::Completeness => ['Completeness', 'Term'],
    Csconstants::ComposerContactName => ['ComposerContact', 'ContactName'],
    Csconstants::ContributorContactName => ['ContributorContact', 'ContactName'],
    Csconstants::CreatorContactName => ['CreatorContact', 'ContactName'],
    Csconstants::CultureOfUse => ['CultureOfUse', 'Term'],
    Csconstants::DecorativeTechnique => ['DecorativeTechnique', 'Term'],
    Csconstants::DepthImperial => ['MainDimension', 'DepthImperial'],
    Csconstants::DepthMetric => ['MainDimension', 'DepthMetric'],
    Csconstants::DesignerName => ['Designer', 'ContactName'],
    Csconstants::DiameterImperial => ['MainDimension', 'DiameterImperial'],
    Csconstants::DiameterMetric => ['MainDimension', 'DiameterMetric'],
    Csconstants::DimensionDescription => ['MainDimension', 'DimensionDescription', 'Term'],
    Csconstants::DirectorContactName => ['DirectorContact', 'ContactName'],
    Csconstants::DistributionCompany => ['DistributionCompany', 'ContactName'],
    Csconstants::EditorContactName => ['EditorContact', 'ContactName'],
    Csconstants::EminentFigureContactName => ['EminentFigureContact', 'ContactName'],
    Csconstants::EminentOrganizationContactName => ['EminentOrganizationContact', 'ContactName'],
    Csconstants::EpochSeries => ['EpochSeries', 'Term'],
    Csconstants::Event => ['Event', 'Term'],
    Csconstants::ExoticNative => ['ExoticNative', 'Term'],
    Csconstants::FieldCollectionMethod => ['FieldCollectionMethod', 'Term'],
    Csconstants::FieldCollectionSourceContactName => ['FieldCollectionSource', 'ContactName'],
    Csconstants::Format => ['Format', 'Term'],
    Csconstants::FormationPeriodSubstrate => ['FormationPeriodSubstrate', 'Term'],
    Csconstants::FullCollectionName => ['Collection', 'FullCollectionName'],
    Csconstants::FullLocationName => ['Location', 'FullLocationName'],
    Csconstants::Genre => ['Genre', 'Term'],
    Csconstants::GeoUnit => ['GeoUnit', 'Term'],
    Csconstants::GeologicalComplexName => ['GeologicalComplexName', 'Term'],
    Csconstants::Habitat => ['Habitat', 'Term'],
    Csconstants::HabitatCommunity => ['HabitatCommunity', 'Term'],
    Csconstants::HeightImperial => ['MainDimension', 'HeightImperial'],
    Csconstants::HeightMetric => ['MainDimension', 'HeightMetric'],
    Csconstants::HistoricCulturalPeriod => ['HistoricCulturalPeriod', 'Term'],
    Csconstants::Horizon => ['Horizon', 'Term'],
    Csconstants::IdentifiedByContactName => ['IdentifiedByContact', 'ContactName'],
    Csconstants::IllustratorContactName => ['IllustratorContact', 'ContactName'],
    Csconstants::ImperialDims => ['MainDimension', 'ImperialDims'],
    Csconstants::InsituFloat => ['InsituFloat', 'Term'],
    Csconstants::InventoryContactName => ['InventoryContact', 'ContactName'],
    Csconstants::LocationName => ['Location', 'LocationName'],
    Csconstants::LocationStatus => ['LocationStatus', 'Term'],
    Csconstants::LocationType => ['LocationType', 'Term'],
    Csconstants::ManufacturingTechnique => ['ManufacturingTechnique', 'Term'],
    Csconstants::Member => ['Member', 'Term'],
    Csconstants::MetricDims => ['MainDimension', 'MetricDims'],
    Csconstants::MovementAuthorizerContactName => ['MovementAuthorizer', 'ContactName'],
    Csconstants::MovementContactName => ['MovementContact', 'ContactName'],
    Csconstants::MovementMethod => ['MovementMethod', 'Term'],
    Csconstants::MovementReason => ['MovementReason', 'Term'],
    Csconstants::NAGPRA => ['NAGPRA', 'Term'],
    Csconstants::NarratorContactName => ['NarratorContact', 'ContactName'],
    Csconstants::ObjectForm => ['ObjectForm', 'Term'],
    Csconstants::ObjectNameCurrency => ['ObjectNameCurrency', 'Term'],
    Csconstants::ObjectNameLevel => ['ObjectNameLevel', 'Term'],
    Csconstants::ObjectNameSystem => ['ObjectNameSystem', 'Term'],
    Csconstants::ObjectNameTitleLanguage => ['ObjectNameTitleLanguage', 'Term'],
    Csconstants::ObjectNameType => ['ObjectNameType', 'Term'],
    Csconstants::ObjectPart => ['ObjectPart', 'Term'],
    Csconstants::ObjectType => ['ObjectType', 'ObjectTypeName'],
    Csconstants::OtherNumberType => ['OtherNumberType', 'Term'],
    Csconstants::PeriodSystem => ['PeriodSystem', 'Term'],
    Csconstants::PermanentFullLocationName => ['PermanentLocation', 'FullLocationName'],
    Csconstants::PermanentLocationName => ['PermanentLocation', 'LocationName'],
    Csconstants::PhotographyContactName => ['PhotographyContact', 'ContactName'],
    Csconstants::ProducerContactName => ['ProducerContact', 'ContactName'],
    Csconstants::ProductionCompanyContactName => ['ProductionCompanyContact', 'ContactName'],
    Csconstants::ProductionDesignerContactName => ['ProductionDesignerContact', 'ContactName'],
    Csconstants::PublisherContactName => ['PublisherContact', 'ContactName'],
    Csconstants::RegistrationStatus => ['RegistrationStatus', 'Term'],
    Csconstants::ResponsibleDepartment => ['ResponsibleDepartment', 'Term'],
    Csconstants::Sex => ['Sex', 'Term'],
    Csconstants::Size => ['Size', 'Term'],
    Csconstants::SpeciesAuthorName => ['SpeciesAuthor', 'ContactName'],
    Csconstants::SpecificClassOfMaterial => ['SpecificClassOfMaterial', 'Term'],
    Csconstants::SquareFeet => ['MainDimension', 'SquareFeet'],
    Csconstants::SquareMeters => ['MainDimension', 'SquareMeters'],
    Csconstants::StorageUnit => ['StorageUnit', 'Term'],
    Csconstants::StratigraphicUnitName => ['StratigraphicUnitName', 'Term'],
    Csconstants::StratigraphicUnitType => ['StratigraphicUnitType', 'Term'],
    Csconstants::StudioContactName => ['StudioContact', 'ContactName'],
    Csconstants::Subgenre => ['Subgenre', 'Term'],
    Csconstants::SubspeciesAuthorName => ['SubspeciesAuthor', 'ContactName'],
    Csconstants::SubspeciesAuthorityContactName => ['SubspeciesAuthorityContact', 'ContactName'],
    Csconstants::SubspeciesFormaAuthorityContactName => ['SubspeciesFormaAuthorityContact', 'ContactName'],
    Csconstants::SubspeciesVarietyAuthorityContactName => ['SubspeciesVarietyAuthorityContact', 'ContactName'],
    Csconstants::Technique => ['Technique', 'Term'],
    Csconstants::Temper => ['Temper', 'Term'],
    Csconstants::ThreatenedEndangeredSpeciesStatus => ['ThreatenedEndangeredSpeciesStatus', 'Term'],
    Csconstants::TitleStatus => ['TitleStatus', 'Term'],
    Csconstants::Transmission => ['Transmission', 'Term'],
    Csconstants::TypeName => ['TypeName', 'Term'],
    Csconstants::TypeSpecimen => ['TypeSpecimen', 'Term'],
    Csconstants::Unit => ['Unit', 'Term'],
    Csconstants::WeightImperial => ['MainDimension', 'WeightImperial'],
    Csconstants::WeightMetric => ['MainDimension', 'WeightMetric'],
    Csconstants::WidthImperial => ['MainDimension', 'WidthImperial'],
    Csconstants::WidthMetric => ['MainDimension', 'WidthMetric'],
    Csconstants::WriterContactName => ['WriterContact', 'ContactName'],
  ];

}
