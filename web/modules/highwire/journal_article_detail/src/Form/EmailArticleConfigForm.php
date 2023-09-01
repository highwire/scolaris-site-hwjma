<?php

/**
 * @file
 * Contains Drupal\journal_article_detail\Form\EmailArticleConfigForm.
 */

namespace Drupal\journal_article_detail\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class EmailArticleConfigForm.
 *
 * @package Drupal\journal_article_detail\Form
 * Use for Email Article backend configuration form : JCOREX-102
 */
class EmailArticleConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'journal_article_detail.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'email_article_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('journal_article_detail.settings');
    
    $form['email_article_display'] = array(
      '#type' => 'checkbox',
      '#title' => t('Display Email Article'),
      '#default_value' => !empty($config->get('email_article_display')) ? $config->get('email_article_display') : 0,
    );

    $form['thanks_msg'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('thanks Message'),
      '#default_value' => !empty($config->get('thanks_msg')) ? $config->get('thanks_msg') : '<p>Thank you for your interest in spreading the word on HWJMA.</p>',
    );

    $form['email_article_note'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Email Article Note'),
      '#default_value' => !empty($config->get('email_article_note')) ? $config->get('email_article_note') : '<p>NOTE: We only request your email address so that the person you are recommending the page to knows that you wanted them to see it, and that it is not junk mail. We do not capture any email address.</p>',
    );

    $form['message_subject'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Message Subject'),
      '#default_value' => !empty($config->get('message_subject')) ? $config->get('message_subject') : '(Your Name) has sent you a message from HWJMA',
    );

    $form['message_body'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Message Body'),
      '#default_value' => !empty($config->get('message_body')) ? $config->get('message_body') : '(Your Name) thought you would like to see the HWJMA web site.',
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
    $this->config('journal_article_detail.settings')
      ->set('email_article_display', $form_state->getValue('email_article_display'))
      ->set('thanks_msg', $form_state->getValue('thanks_msg'))
      ->set('email_article_note', $form_state->getValue('email_article_note'))
      ->set('message_subject', $form_state->getValue('message_subject'))
      ->set('message_body', $form_state->getValue('message_body'))
      ->save();
  }
}
