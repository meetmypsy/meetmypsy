<?php

/**
 * @file
 * contains \Drupal\calendarly\Controller\Calendarly
 */

namespace Drupal\calendarly\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Provides route responses.
 */
class Calendarly extends ControllerBase {

  /**
   * Builds the array with romote data.
   *
   * @return array
   *   An array with remote data.
   */
  public function overview() {
    $items = [];
    $routes = [
      'calendarly.csv_export',
      'calendarly.questions',
      'calendarly.test'
    ];
    foreach($routes as $route){
      $url = Url::fromRoute($route);
      $items[] = Link::fromTextAndUrl($url->toString(), $url);;
    }
    return [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => $items,
    ];
  }

}
