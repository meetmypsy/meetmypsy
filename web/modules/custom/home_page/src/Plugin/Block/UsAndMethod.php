<?php

namespace Drupal\home_page\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'us_and_method' Block.
 *
 * @Block(
 *   id = "us_and_method",
 *   admin_label = @Translation("Us and Method block"),
 *   category = @Translation("Home Page"),
 * )
 */
class UsAndMethod extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $output = [
      '#type' => 'container'
    ];
    $config = $this->getConfiguration();

    $output['our_method'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['our-method'],
      ],
    ];
    if (!empty($config['our_method_title'])) {
      $output['our_method']['our_method_title'] = [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#value' => $config['our_method_title'],
        '#attributes' => ['class' => ['title']]
      ];
    }
    if (!empty($config['our_method_description'])) {
      $output['our_method']['our_method_description'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['our-method-description']
        ]
      ];
      $output['our_method']['our_method_description']['txt'] = [
        '#type' => 'processed_text',
        '#text' => $config['our_method_description']['value'],
        '#format' => $config['our_method_description']['format'],
      ];
    }


    $output['who_we_are'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['who-we-are'],
      ],
    ];
    if (!empty($config['who_we_are_title'])) {
      $output['who_we_are']['who_we_are_title'] = [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#value' => $config['who_we_are_title'],
        '#attributes' => ['class' => ['title']]
      ];
    }
    if (!empty($config['who_we_are_description'])) {
      $output['who_we_are']['who_we_are_description'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['who-we-are-description']
        ]
      ];
      $output['who_we_are']['who_we_are_description']['txt'] = [
        '#type' => 'processed_text',
        '#text' => $config['who_we_are_description']['value'],
        '#format' => $config['who_we_are_description']['format']
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

    $form['who_we_are_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Who we are title'),
      '#size' => 80,
      '#default_value' => $config['who_we_are_title'] ?? '',
    ];
    $form['who_we_are_description'] = [
      '#type' => 'text_format',
      '#rows' => 10,
      '#title' => $this->t('Who we are description'),
      '#default_value' => $config['who_we_are_description']['value'] ?? '',
      '#format' => $config['who_we_are_description']['format'] ?? 'full_html',
    ];
    $form['our_method_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Our Method title'),
      '#size' => 80,
      '#default_value' => $config['our_method_title'] ?? '',
    ];
    $form['our_method_description'] = [
      '#type' => 'text_format',
      '#rows' => 10,
      '#title' => $this->t('Our Method description'),
      '#default_value' => $config['our_method_description']['value'] ?? '',
      '#format' => $config['our_method_description']['format'] ?? 'full_html',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['who_we_are_title'] = $values['who_we_are_title'];
    $this->configuration['who_we_are_description'] = $values['who_we_are_description'];
    $this->configuration['our_method_title'] = $values['our_method_title'];
    $this->configuration['our_method_description'] = $values['our_method_description'];
  }

}
