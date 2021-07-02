<?php

/**
 * @file
 * Contains \Drupal\calendarly\Form\csvExportForm
 */

namespace Drupal\calendarly\Form;

use Drupal\calendarly\CalendarlyFetcher;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\Messenger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides an Calendarly csv export form. 
 */
class csvExportForm extends FormBase {

  /**
   * The calendarly fetcher service.
   *
   * @var \Drupal\calendarly\CalendarlyFetcher
   */
  protected $calendarlyFetcher;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $routematch_service = $container->get('calendarly.fetcher');
    $messenger = $container->get('messenger');
    $entity_type_manager = $container->get('entity_type.manager');
    $date_formatter = $container->get('date.formatter');
    $config_factory = $container->get('config.factory');

    return new static($routematch_service, $messenger, $entity_type_manager, $date_formatter, $config_factory);
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(CalendarlyFetcher $calendarlyfetcher, Messenger $messenger, EntityTypeManagerInterface $entity_type_manager, DateFormatter $dateFormatter, ConfigFactoryInterface $config_factory) {
    $this->calendarlyFetcher = $calendarlyfetcher;
    $this->messenger = $messenger;
    $this->entityTypeManager = $entity_type_manager;
    $this->dateFormatter = $dateFormatter;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'calendarly_csv_export_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get praticiens
    $praticiens = $this
      ->entityTypeManager
      ->getStorage('node')
      ->loadByProperties(['type' => 'praticien', 'status' => 1]);
    if (empty($praticiens)) {
      $this->messenger->addError(t('No Practitioner file is published yet'));
      return;
    }
    $options = [];
    foreach ($praticiens as $praticien) {
      $options[$praticien->id()] = $praticien->label();
    }

    $form['praticiens'] = [
      '#title' => t('Practitioners'),
      '#type' => 'checkboxes',
      '#required' => TRUE,
      '#options' => $options,
      '#default_value' => array_keys($options)
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Export csv')
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $praticiens = $this
      ->entityTypeManager
      ->getStorage('node')
      ->loadMultiple($form_state->getValue('praticiens'));
    // Start using PHP's built in file handler functions to create a temporary file.
    $handle = fopen('php://temp', 'w+');

    // Set up the header that will be displayed as the first line of the CSV file.
    $header = $this->getHeader();
    // Add the header as the first line of the CSV.
    fputcsv($handle, $header);
    // Find and load all of the Article nodes we are going to include

    $all_data = $this->getData($praticiens);
    foreach ($all_data as $data) {

      // Add the data we exported to the next line of the CSV>
      fputcsv($handle, array_values($data));
    }
    // Reset where we are in the CSV.
    rewind($handle);

    // Retrieve the data from the file handler.
    $csv_data = stream_get_contents($handle);

    // Close the file handler since we don't need it anymore.  We are not storing
    // this file anywhere in the filesystem.
    fclose($handle);

    // This is the "magic" part of the code.  Once the data is built, we can
    // return it as a response.
    $response = new Response();

    // By setting these 2 header options, the browser will see the URL
    // used by this Controller to return a CSV file called "report.csv".
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="report.csv"');
    $response->headers->set('Expires', 0);
    $response->headers->set('Content-Transfer-Encoding', 'binary');
    // This line physically adds the CSV data we created 
    $response->setContent($csv_data);
    //Comment next line to debug submitted value
    $form_state->setResponse($response);
  }

  /**
   * Helper method to format questions and answers.
   *
   * @return string
   */
  public function formatQA($questions_answers) {
    $output = [];
    if (!empty($questions_answers)) {
      foreach ($questions_answers as $qa) {
        $output[$qa['question']] = $qa['answer'];
      }
    }
    return $output;
  }

  /**
   * Helper method to get header of csv.
   *
   * @return array
   */
  public function getHeader() {
    $header = [
      'Nom',
      'Prénom',
      'Mail',
      'Praticien',
      'Date',
      'Heure'
    ];
    $questions = $this->configFactory->get('calendarly.questions')->get('all_questions');
    $header = array_merge($header, array_filter($questions));
    return $header;
  }

  /**
   * Helper method to get data to export in csv.
   *
   * @param type $praticiens
   * @return array
   */
  public function getData($praticiens) {
    $all_data = [];
    foreach ($praticiens as $praticien) {
      $token = $praticien->field_calendarly_token->getString();
      $events = $this->calendarlyFetcher->fetch($token, 'events');
      $events = $events[0]['collection'];
      if (!empty($events)) {
        foreach ($events as $event) {
          $participants = $this->calendarlyFetcher->fetch($token, 'forced_uri', $event['uri'] . '/invitees');
          // There is only one participant in an event.
          $participant = $participants[0]['collection'][0];
          $start_time_timestamp = strtotime($event['start_time']);
          $values = [
            $participant['last_name'] ?? $participant['name'],
            $participant['first_name'] ?? $participant['name'],
            $participant['email'],
            $praticien->label(),
            $this->dateFormatter->format($start_time_timestamp, 'custom', 'd/m/Y'),
            $this->dateFormatter->format($start_time_timestamp, 'custom', 'H:i'),
          ];
          $questions = $this->configFactory->get('calendarly.questions')->get('all_questions');
          $questions = array_filter($questions);
          $answers = $this->formatQA($participant['questions_and_answers']);
          foreach ($questions as $question) {
            $values[] = $answers[$question] ?? '';
          }
          $all_data[] = array_combine($this->getHeader(), $values);
        }
      }
    }
    // Sort by Date
    $col = array_column($all_data, 'Date');
    array_multisort($col, SORT_ASC, $all_data);
    return $all_data;
  }

}
