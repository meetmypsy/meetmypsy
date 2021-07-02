<?php

namespace Drupal\home_page\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'footer' Block.
 *
 * @Block(
 *   id = "footer",
 *   admin_label = @Translation("Footer block"),
 *   category = @Translation("Home Page"),
 * )
 */
class Footer extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $output = [];
    $config = $this->getConfiguration();
    if (!empty($config['footer'])) {
      $output['footer'] = [
        '#type' => 'processed_text',
        '#text' => $config['footer']['value'],
        '#format' => $config['footer']['format'],
      ];
    }
    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['footer'] = [
      '#type' => 'text_format',
      '#rows' => 10,
      '#title' => $this->t('Footer'),
      '#default_value' => $config['footer']['value'] ?? '',
      '#format' => $config['footer']['format'] ?? 'full_html',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['footer'] = $values['footer'];
  }

}
