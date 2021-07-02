<?php

namespace Drupal\home_page\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'how_it_works' Block.
 *
 * @Block(
 *   id = "how_it_works",
 *   admin_label = @Translation("How it Works block"),
 *   category = @Translation("Home Page"),
 * )
 */
class HowItWorks extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $output = [];
    $config = $this->getConfiguration();
    if (!empty($config['title'])) {
      $output['title'] = [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#value' => $config['title'],
        '#attributes' => ['class' => ['title']]
      ];
    }
    for ($i = 1; $i <= $this->getStepsCount(); $i++) {
      if (!empty($config['step' . $i])) {
        $items[] = $config['step' . $i];
      }
    }
    $output['steps'] = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => $items
    ];
    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('internal title'),
      '#description' => $this->t('Title in the block content'),
      '#size' => 40,
      '#default_value' => $config['title'] ?? '',
    ];
    for ($i = 1; $i <= $this->getStepsCount(); $i++) {
      $form['step' . $i] = [
        '#type' => 'textarea',
        '#title' => $this->t('Step %num', ['%num' => $i]),
        '#default_value' => $config['step' . $i] ?? '',
        '#rows' => 2
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();

    $this->configuration['title'] = $values['title'];
    for ($i = 1; $i <= $this->getStepsCount(); $i++) {
      $this->configuration['step' . $i] = $values['step' . $i];
    }
  }

  /**
   * Helper to get number of steps.
   *
   */
  public function getStepsCount() {
    return 5;
  }

}
