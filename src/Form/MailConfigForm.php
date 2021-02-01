<?php

namespace Drupal\dyniva_mail\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dyniva_helper\Helper;
use Drupal\dyniva_mail\MailHelper;

/**
 * Class MailConfigForm.
 */
class MailConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'dyniva_mail.mailconfig',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mail_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $configs = $this->config('dyniva_mail.mailconfig')->get('mail');
    unset($configs['actions']);
    // Gather the number of names in the form already.
    $num_names = $form_state->get('num_names');
    // We have to ensure that there is at least one name field.
    if ($num_names === NULL) {
      $name_field = $form_state->set('num_names', count($configs)+1);
      $num_names = count($configs)+1;
    }

    $form['#tree'] = TRUE;
    $form['mail'] = [
      '#type' => 'fieldset',
      '#prefix' => '<div id="mail-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];

    // each all templates.
    for ($i = 0; $i < $num_names; $i++) {
      $form['mail'][$i] = [
        '#title' => 'Mail template info',
        '#type' => 'fieldset',
      ];
      $form['mail'][$i]['id'] = [
        '#title' => 'Mail ID',
        '#type'  => 'textfield',
        '#default_value' => $configs[$i]['id'] ?? ''
      ];

      $form['mail'][$i]['title'] = [
        '#title' => 'Mail Title',
        '#type'  => 'textfield',
        '#default_value' => $configs[$i]['title'] ?? ''
      ];

      $form['mail'][$i]['body'] = [
        '#title' => 'Mail Body',
        '#type'  => 'textarea',
        '#default_value' => $configs[$i]['body'] ?? ''
      ];
    }

    $form['mail']['actions'] = [
      '#type' => 'actions',
    ];
    $form['mail']['actions']['add_name'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add one more'),
      '#submit' => ['::addOne'],
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => 'mail-fieldset-wrapper',
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    return $form;
  }

  public function addOne(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('num_names');
    $add_button = $name_field + 1;
    $form_state->set('num_names', $add_button);
    // Since our buildForm() method relies on the value of 'num_names' to
    // generate 'name' form elements, we have to tell the form to rebuild. If we
    // don't do this, the form builder will not call buildForm().
    $form_state->setRebuild();
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['mail'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config_value = Helper::clearEmptyArray($form_state->getValue('mail'));
    unset($config_value['actions']);
    foreach ($config_value as $k => $v) {
      if(empty($v)) {
        unset($config_value[$k]);
      }
    }
    \Drupal::service('config.factory')->getEditable('dyniva_mail.mailconfig')
      ->set('mail', $config_value)
      ->save();
  }

}
