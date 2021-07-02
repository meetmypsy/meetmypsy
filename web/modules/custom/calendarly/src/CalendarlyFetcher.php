<?php

/**
 * @file
 * Contains Drupal\calendarly\CalendarlyFetcher service
 */

namespace Drupal\calendarly;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Messenger\Messenger;
use GuzzleHttp\Exception\RequestException;

/**
 * Fetches Calendarly data.
 *
 */
class CalendarlyFetcher {

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * {@inheritdoc}
   */
  public function __construct(Messenger $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * Fetches data from calendarly.
   *
   * @param string $token 
   *        the token to use.
   * @param string $type
   *        the type of data to request : 
   *         user
   *         events
   *         participants_first_event
   *         event_types
   *         forced_uri
   * @param string $uri
   *        used with 'forced_uri' type.
   * @return array
   */
  public function fetch($token, $type = 'user', $uri = null) {
    $client = \Drupal::httpClient();
    switch ($type) {
      case 'user':
        $url = 'https://api.calendly.com/users/me';
        break;
      case 'events':
        //Get user uri first.
        $calendarly_user = $this->fetch($token, 'user');
        $url = "https://api.calendly.com/scheduled_events?user=" . $calendarly_user[0]['resource']['uri'];
        break;
      case 'participants_first_event':
        //Get events of user.
        $calendarly_events = $this->fetch($token, 'events');
        $first_event_uri = $calendarly_events[0]['collection'][0]['uri'];
        $url = $first_event_uri . '/invitees';
        break;
      case 'event_types':
        //Get user uri first.
        $calendarly_user = $this->fetch($token, 'user');
        //Get events fo user.
        $url = "https://api.calendly.com/event_types?user=" . $calendarly_user[0]['resource']['uri'];
        break;
      case 'forced_uri':
        //Get events fo user.
        $url = $uri;
        break;
    }
    try {
      $request = $client->get(
        $url, [
        'verify' => FALSE,
        'headers' => ['Authorization' => "Bearer $token"],
        ]
      );
      $body = $request->getBody();
      return [Json::decode($body)];
    }
    catch (RequestException $e) {
      $this->messenger->addError($e->getMessage());
    }
  }

}
