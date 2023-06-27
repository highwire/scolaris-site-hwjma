<?php

/**
 * @file
 * Contains Drupal\hwjma_basic_config\Form\SettingsForm.
 */

namespace Drupal\hwjma_basic_config\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SettingsForm.
 *
 * @package Drupal\hwjma_basic_config\Form
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'hwjma_basic_config.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('hwjma_basic_config.settings');

    $form['footer_facebook'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Footer Facebook'),
      '#default_value' => $config->get('footer_facebook'),
    );

    $form['footer_twitter'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Footer Twitter'),
      '#default_value' => $config->get('footer_twitter'),
    );

    $form['footer_youtube'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Footer Youtube'),
      '#default_value' => $config->get('footer_youtube'),
    );

    $form['footer_linkedin'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Footer Linkedin'),
      '#default_value' => $config->get('footer_linkedin'),
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

    $this->config('hwjma_basic_config.settings')
      ->set('footer_twitter', $form_state->getValue('footer_twitter'))
      ->set('footer_youtube', $form_state->getValue('footer_youtube'))
      ->set('footer_facebook', $form_state->getValue('footer_facebook'))
      ->set('footer_linkedin', $form_state->getValue('footer_linkedin'))
      ->save();
  }

}