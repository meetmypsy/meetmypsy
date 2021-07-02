<?php

/**
 * @file
 * Contains \Drupal\calendarly\Form\TestForm
 */

namespace Drupal\calendarly\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Render\Markup;
use Drupal\calendarly\CalendarlyFetcher;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an Calendarly test form. 
 */
class TestForm extends FormBase {

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
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * The private tempstore service.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStore;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $routematch_service = $container->get('calendarly.fetcher');
    $messenger = $container->get('messenger');
    $modulehandler = $container->get('module_handler');
    // By default, data is stored for one week (604800 seconds) before expiring
    $tempstore = $container->get('tempstore.private');
    return new static($routematch_service, $messenger, $modulehandler, $tempstore);
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(CalendarlyFetcher $calendarlyfetcher, Messenger $messenger, ModuleHandler $modulehandler, PrivateTempStoreFactory $tempstore) {
    $this->calendarlyFetcher = $calendarlyfetcher;
    $this->messenger = $messenger;
    $this->moduleHandler = $modulehandler;
    $this->tempStore = $tempstore;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'calendarly_test_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['token'] = [
      '#title' => t('Token'),
      '#type' => 'textarea',
      '#rows' => 3,
      '#required' => TRUE
    ];
    $store = $this->tempStore->get('calendarly');
    $stored_type = $store->get('test_calendarly_type');
    $form['type'] = [
      '#title' => t('Type'),
      '#type' => 'radios',
      '#default_value' => isset($stored_type) ? $stored_type : 'user',
      '#options' => array(
        'user' => $this->t('User'),
        'events' => $this->t('Events'),
        'participants_first_event' => $this->t('Participants of first event'),
        'event_types' => $this->t('Event types'),
      )
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Test')
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $token = $form_state->getValue('token');
    $type = $form_state->getValue('type');
    // Store chosen value to select it by default next time we load the form.
    $store = $this->tempStore->get('calendarly');
    $store->set('test_calendarly_type', $type);
    $data = $this->calendarlyFetcher->fetch($token, $type);
    if ($data) {
      if ($this->moduleHandler->moduleExists('devel')) {
        dpm($data);
      }
      else {
        $rendered_message = Markup::create('<pre>' . print_r($data, true) . '</pre>');
        $this->messenger->addMessage($rendered_message);
      }
    }
  }

}
