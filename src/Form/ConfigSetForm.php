<?php

/**
 * @file
 * Contains Drupal\autosync\Form\ConfigSetForm.
 */

namespace Drupal\autosync\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ConfigSetForm.
 *
 * @package Drupal\autosync\Form
 */
class ConfigSetForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'autosync.config_set_form_config'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'config_set_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('autosync.config_set_form_config');
    $form['token'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Token'),
      '#description' => $this->t('Set the token provided in getconfig.co'),
      '#default_value' => $config->get('token'),
    );

    return parent::buildForm($form, $form_state);
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
    parent::submitForm($form, $form_state);

    $this->config('autosync.config_set_form_config')
      ->set('token', $form_state->getValue('token'))
      ->save();
  }
}
