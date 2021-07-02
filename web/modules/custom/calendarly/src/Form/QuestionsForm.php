<?php

/**
 * @file
 * Contains \Drupal\calendarly\Form\QuestionsForm
 */
namespace Drupal\calendarly\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an Calendarly test form. 
 */
class QuestionsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'calendarly_questions_form';
  }

   /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'calendarly.questions',
    ];
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('calendarly.questions');
    $stored_questions = $config->get('all_questions');
    // Gather the number of questions in the form already.
    $num_questions = $form_state
      ->get('num_questions');

    // We have to ensure that there is at least one question field.
    if ($num_questions === NULL) {
      if (!empty($stored_questions)) {
        $num_questions = count($stored_questions);
      }
      else {
        $num_questions = 1;
      }
      $form_state->set('num_questions', $num_questions);
    }
    $form['#tree'] = TRUE;
    $form['questions_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this
        ->t('Questions for calendarly csv export'),
      '#prefix' => '<div id="questions-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];
    for ($i = 0; $i < $num_questions; $i++) {
      $form['questions_fieldset']['question'][$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Question %i', ['%i' => $i+1]),
        '#default_value' => $stored_questions[$i] ??''
      ];
    }
    $form['questions_fieldset']['actions'] = [
      '#type' => 'actions',
    ];
    $form['questions_fieldset']['actions']['add_question'] = [
      '#type' => 'submit',
      '#value' => $this
        ->t('Add one more question'),
      '#submit' => [
        '::addOne',
      ],
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => 'questions-fieldset-wrapper',
      ],
    ];

    // If there is more than one question, add the remove button.
    if ($num_questions > 1) {
      $form['questions_fieldset']['actions']['remove_question'] = [
        '#type' => 'submit',
        '#value' => $this
          ->t('Remove last question'),
        '#submit' => [
          '::removeCallback',
        ],
        '#ajax' => [
          'callback' => '::addmoreCallback',
          'wrapper' => 'questions-fieldset-wrapper',
        ],
      ];
    }
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this
        ->t('Submit'),
    ];
    return $form;
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the questions in it.
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['questions_fieldset'];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    $question_field = $form_state
      ->get('num_questions');
    $add_button = $question_field + 1;
    $form_state
      ->set('num_questions', $add_button);

    // Since our buildForm() method relies on the value of 'num_questions' to
    // generate 'question' form elements, we have to tell the form to rebuild. If we
    // don't do this, the form builder will not call buildForm().
    $form_state
      ->setRebuild();
  }

  /**
   * Submit handler for the "remove last question" button.
   *
   * Decrements the max counter and causes a form rebuild.
   */
  public function removeCallback(array &$form, FormStateInterface $form_state) {
    $question_field = $form_state
      ->get('num_questions');
    if ($question_field > 1) {
      $remove_button = $question_field - 1;
      $form_state
        ->set('num_questions', $remove_button);
    }

    // Since our buildForm() method relies on the value of 'num_questions' to
    // generate 'question' form elements, we have to tell the form to rebuild. If we
    // don't do this, the form builder will not call buildForm().
    $form_state
      ->setRebuild();
  }

  /**
   * Final submit handler.
   *
   * Reports what values were finally set.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state
      ->getValue([
      'questions_fieldset',
      'question',
    ]);
    $values = array_filter($values);
    $this->config('calendarly.questions')->set('all_questions', $values)->save();
    parent::submitForm($form, $form_state);
  }

}
