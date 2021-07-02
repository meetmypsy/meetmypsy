<?php

namespace Drupal\home_page\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'banner_presentation' Block.
 *
 * @Block(
 *   id = "banner_presentation",
 *   admin_label = @Translation("Banner presentation block"),
 *   category = @Translation("Home Page"),
 * )
 */
class BannerPresentation extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $output = [
      '#type' => 'container'
    ];
    $config = $this->getConfiguration();
    if (!empty($config['quote'])) {
      $output['quote'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['quote'],
        ],
      ];
      $output['quote']['txt'] = ['#markup' => $config['quote']];
    }
    if (!empty($config['description'])) {
      $output['description'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['desc'],
        ],
      ];
      $output['description']['txt'] = [
        '#type' => 'processed_text',
        '#text' => $config['description']['value'],
        '#format' => $config['description']['format'],
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

    $form['quote'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Quote text'),
      '#description' => $this->t('Quote that will display on the image'),
      '#size' => 80,
      '#default_value' => $config['quote'] ?? '',
    ];
    $form['description'] = [
      '#type' => 'text_format',
      '#rows' => 10,
      '#title' => $this->t('Description'),
      '#description' => $this->t('Text that will display after the image'),
      '#default_value' => $config['description']['value'] ?? '',
      '#format' => $config['description']['format'] ?? 'full_html',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['quote'] = $values['quote'];
    $this->configuration['description'] = $values['description'];
  }

}
