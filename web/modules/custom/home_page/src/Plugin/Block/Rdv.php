<?php

namespace Drupal\home_page\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'rdv' Block.
 *
 * @Block(
 *   id = "rdv",
 *   admin_label = @Translation("Rdv block"),
 *   category = @Translation("All Pages"),
 * )
 */
class Rdv extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $output = [];
    $config = $this->getConfiguration();
    if (!empty($config['rdv'])) {
      $output['rdv'] = [
        '#type' => 'processed_text',
        '#text' => $config['rdv']['value'],
        '#format' => $config['rdv']['format'],
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

    $form['rdv'] = [
      '#type' => 'text_format',
      '#rows' => 10,
      '#title' => $this->t('Rdv'),
      '#default_value' => $config['rdv']['value'] ?? '',
      '#format' => $config['rdv']['format'] ?? 'full_html',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['rdv'] = $values['rdv'];
  }

}
