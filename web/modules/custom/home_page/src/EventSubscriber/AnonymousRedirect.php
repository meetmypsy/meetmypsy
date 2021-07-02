<?php

namespace Drupal\home_page\EventSubscriber;

use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AnonymousRedirect implements EventSubscriberInterface {

  public function __construct() {
    $this->account = \Drupal::currentUser();
  }

  public static function getSubscribedEvents() {
    return [KernelEvents::REQUEST => [['redirectionLogin']]];
  }

  public function redirectionLogin(GetResponseEvent $event) {
    $request = $event->getRequest();
    $is_anonyme = $this->account->isAnonymous();
    $is_praticiens_page = $request->attributes->get('_route') == 'view.praticiens.page_1';

    if ($is_anonyme && $is_praticiens_page) {
      $response = new RedirectResponse(Url::fromRoute('user.login')->toString(), 301);
      $event->setResponse($response);
    }
  }

}
