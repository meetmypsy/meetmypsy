<?php

namespace Drupal\calendarly\Plugin\Field\FieldFormatter;

use Drupal\calendarly\CalendarlyFetcher;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Markup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'calendarly_widget' formatter.
 *
 * @FieldFormatter(
 *   id = "calendarly_widget",
 *   label = @Translation("Calendarly Widget"),
 *   field_types = {
 *     "string_long"
 *   }
 * )
 */
class CalendarlyWidgetFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The calendarly fetcher service
   *
   * @var \Drupal\calendarly\CalendarlyFetcher
   */
  protected $calendarlyFetcher;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      // Add any services you want to inject here
      $container->get('calendarly.fetcher')
    );
  }

  /**
   * Construct a MyFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   Defines an interface for entity field definitions.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\calendarly\CalendarlyFetcher $calendarlyFetcher
   *   Calendarly fetcher service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, CalendarlyFetcher $calendarlyFetcher) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->calendarlyFetcher = $calendarlyFetcher;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Displays the calendarly widget.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    foreach ($items as $delta => $item) {
      //$markup = $this->getGlobalEventsIntegrationCode($item->value);
      $markup = $this->getSubEventsIntegrationCode($item->value);
      $element[$delta] = ['#markup' => Markup::create($markup)];
    }

    return $element;
  }

  /**
   * Get the global events page markup.
   *
   * @param type $token
   * @return string
   */
  public function getGlobalEventsIntegrationCode($token) {
    $calendarly_data = $this->calendarlyFetcher->fetch($token);
    $scheduling_url = $calendarly_data[0]['resource']['scheduling_url'];
    // Render each element as markup.
    $markup = '
        <!-- Début de widget en ligne Calendly -->       
        <div class="calendly-inline-widget" data-url="' . $scheduling_url . '" style="min-width:320px;height:650px;"></div>
        <script type="text/javascript" src="https://assets.calendly.com/assets/external/widget.js" async></script>
        <!-- Fin de widget en ligne Calendly -->';
    return $markup;
  }

  /**
   * Get sub events integration markup.
   *
   * @param type $token
   * @return string
   */
  public function getSubEventsIntegrationCode($token) {
    $markup = '';
    $calendarly_data = $this->calendarlyFetcher->fetch($token, 'event_types');
    if (!empty($calendarly_data[0]['collection'])) {
      foreach ($calendarly_data[0]['collection'] as $event_type) {
        if ($event_type['active']) {
          $scheduling_url = $event_type['scheduling_url'];
          // Render each element as markup.
          $markup .= '
        <!-- Début de widget en ligne Calendly -->       
        <div class="calendly-inline-widget" data-url="' . $scheduling_url . '" style="min-width:320px;height:650px;"></div>
        <script type="text/javascript" src="https://assets.calendly.com/assets/external/widget.js" async></script>
        <!-- Fin de widget en ligne Calendly -->';
        }
      }
    }

    return $markup;
  }

}
