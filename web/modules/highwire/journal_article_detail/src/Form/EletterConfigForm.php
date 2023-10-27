<?php

/**
 * @file
 * Contains Drupal\journal_article_detail\Form\EletterConfigForm.
 */

namespace Drupal\journal_article_detail\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class EletterConfigForm.
 *
 * @package Drupal\journal_article_detail\Form
 * Use for Altmetric and Usage Stats backend configuration form : JCOREX-102
 */
class EletterConfigForm extends ConfigFormBase {

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
    return 'eletter_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('journal_article_detail.settings');

    $form = array();
    $site_mail = \Drupal::config('system.site')->get('mail');
    $site_name = \Drupal::config('system.site')->get('name');
    $form['highwire_rr_email_sender'] = array(
      '#type' => 'textfield',
      '#title' => t('E-mail to send the emails from:'),
      '#default_value' => !empty($config->get('highwire_rr_email_sender')) ? $config->get('highwire_rr_email_sender') : $site_mail,
    );

    $form['highwire_rr_email_subject'] = array(
      '#type' => 'textfield',
      '#title' => t('Subject Line for the response emails:'),
      '#default_value' => !empty($config->get('highwire_rr_email_subject')) ? $config->get('highwire_rr_email_subject') : 'Thank you for your response to',
    );

    // Defining Constants for eLetter response submit
    $highwire_rr_email_response_text = '<p>Thank you for your submission. Below is a copy of your eLetter as we received it. Your eLetter, if accepted, should be
    viewable within a few days.</p>

    <p>Sincerely,<br />
    The Editorial staff of !journal_name</p>
    ----------------------------------------
    <p>!article_link</p>
    <p>The eLetter <em>!eletter_title</em> was submitted on !submitted_date:</p>
    <p>!eletter_text</p>';

    $form['highwire_rr_email_response_sent'] = array(
      '#type' => 'textarea',
      '#title' => t('Message to send to the sender of an eLetter:'),
      '#default_value' => !empty($config->get('highwire_rr_email_response_sent')) ? $config->get('highwire_rr_email_response_sent') : $highwire_rr_email_response_text,
    );

    // E-letter submission message.
    $highwire_e_letter_submission_message = 'Thank you for your response. We intend to publish as rapidly as possible all responses that contribute substantially to the topic under discussion.';
    $form['highwire_e_letter_submission_message'] = array(
      '#type' => 'textarea',
      '#title' => t('Message to display when user submits an eLetter:'),
      '#default_value' => !empty($config->get('highwire_e_letter_submission_message')) ? $config->get('highwire_e_letter_submission_message') : $highwire_e_letter_submission_message,
    );

    // Notification for Moderator.
    $form['moderator_notification'] = array(
      '#title' => t('Notification for Moderators'),
      '#type' => 'fieldset',
    );

    $moderator_distribution_email_list = $site_mail;
    $form['moderator_notification']['moderator_distribution_email_list'] = array(
      '#type' => 'textarea',
      '#title' => t('Moderator Distribution Email'),
      '#required' => TRUE,
      '#description' => t('The moderator distribution list is created externally to the journal site. The moderator is a publisher employee who manages which eLetters get published, and which do not. (If multiple emails required, please enter one email per line)'),
      '#default_value' => !empty($config->get('moderator_distribution_email_list')) ? $config->get('moderator_distribution_email_list') : $moderator_distribution_email_list,
    );

    $moderator_site_feedback_email = $site_mail;
    $form['moderator_notification']['moderator_site_feedback_email_list'] = array(
      '#type' => 'textarea',
      '#title' => t('Site Feedback Email Address'),
      '#required' => TRUE,
      '#description' => t('Default site feedback email address'),
      '#default_value' => !empty($config->get('moderator_site_feedback_email_list')) ? $config->get('moderator_site_feedback_email_list') : $moderator_site_feedback_email,
    );

    $moderator_notification_from_address = $site_name . ' Response Notification <!site_feeback_email_address>';
    $form['moderator_notification']['moderator_notification_from_address'] = array(
      '#type' => 'textarea',
      '#title' => t('E-mail to send the emails from'),
      '#required' => TRUE,
      '#description' => t('From address, a configurable parameter, to send a moderator notification from this'),
      '#default_value' => !empty($config->get('moderator_notification_from_address')) ? $config->get('moderator_notification_from_address') : $moderator_notification_from_address,
    );

    $moderator_notification_to_address = $site_name . ' Response Moderator <!moderator_distribution_email_list>';
    $form['moderator_notification']['moderator_notification_to_address'] = array(
      '#type' => 'textarea',
      '#title' => t('E-mail to send the emails to'),
      '#required' => TRUE,
      '#description' => t('To address, a configurable parameter, to send a moderator notification to this configured value'),
      '#default_value' => !empty($config->get('moderator_notification_to_address')) ? $config->get('moderator_notification_to_address') : $moderator_notification_to_address,
    );

    $moderator_notification_email_subject = $site_name . ' response: "[node:title]""';
    $form['moderator_notification']['moderator_notification_email_subject'] = array(
      '#type' => 'textarea',
      '#title' => t('Subject Line for the Moderator response emails'),
      '#required' => TRUE,
      '#description' => t('Email subject line a configurable parameter for the moderator notification alert email'),
      '#default_value' => !empty($config->get('moderator_notification_email_subject')) ? $config->get('moderator_notification_email_subject') : $moderator_notification_email_subject,
    );

    $moderator_notification_response_text = '<p>A new eLetter has been submitted to !article_title and is awaiting moderation.</p>
    <p>!article_link</p>
    <p>The eLetter was submitted on [node:created:custom:d m Y]:</p>
    <p>eLetter copy: [site:url]!eLetter_link_copy</p>
    <p>!eletter_text</p>';
    $form['moderator_notification']['moderator_notification_response_text'] = array(
      '#type' => 'textarea',
      '#title' => t('Message to send to the Moderator of an eLetter'),
      '#required' => TRUE,
      '#description' => t('Response text a configurable parameter that goes in the body of the moderator notification email'),
      '#default_value' => !empty($config->get('moderator_notification_response_text')) ? $config->get('moderator_notification_response_text') : $moderator_notification_response_text,
    );

    // Notification on eLetter publication for Article authors and eLetter author.
    $form['eLetter_publication_notification'] = array(
      '#title' => t('Notification on eLetter publication for Article authors and eLetter author'),
      '#type' => 'fieldset',
    );

    $form['eLetter_publication_notification']['eletter_rule_action_flag'] = array(
      '#title' => t('Rule Action Status'),
      '#type' => 'checkbox',
      '#description' => t('If selected then rule action will be enabled for "Set eLetter to Release status" button, otherwise no'),
      '#default_value' => !empty($config->get('eletter_rule_action_flag')) ? $config->get('eletter_rule_action_flag') : 0,
    );

    $from_address_to_eletterauthor = $site_mail;
    $form['eLetter_publication_notification']['eLetter_publication_notification_from_address_to_eletterauthor'] = array(
      '#type' => 'textarea',
      '#title' => t('E-mail to send the emails from to eLetter Author'),
      '#required' => TRUE,
      '#description' => t('From address a configurable parameter, to send a published notification from this to eLetter author'),
      '#element_validate' => array('highwire_responses_moderation_email_validate'),
      '#default_value' => !empty($config->get('eLetter_publication_notification_from_address_to_eletterauthor')) ? $config->get('eLetter_publication_notification_from_address_to_eletterauthor') : $from_address_to_eletterauthor,
    );

    $subject_to_eletterauthor = 'Your Response has been published';
    $form['eLetter_publication_notification']['eLetter_publication_notification_subject_to_eletterauthor'] = array(
      '#type' => 'textarea',
      '#title' => t('Subject line for the notification to eLetter Author'),
      '#required' => TRUE,
      '#description' => t('Email subject line a configurable parameter for the published notification alert email'),
      '#default_value' => !empty($config->get('eLetter_publication_notification_subject_to_eletterauthor')) ? $config->get('eLetter_publication_notification_subject_to_eletterauthor') : $subject_to_eletterauthor,
    );

    $eletterauthor_response_text = '<p>Dear !article_author_firstname_lastname,</p>

    <p>An eLetter has been published on [node:field-highwire-c-response-to:field-highwire-a-journal:title]\'s web site. To view it, navigate to your article and click on "eLetter" tab or click the link below. </p>
    
    <p>eLetter: "[node:title]"</p>
    
    <p>eLetter URL: [node:field-highwire-c-response-to:url:absolute] </p>
    
    <p>Sincerely,</p>
    <p>The Editorial Staff of [node:field-highwire-c-response-to:field-highwire-a-journal:title]</p>';
    $form['eLetter_publication_notification']['eLetter_publication_notification_eletterauthor_response_text'] = array(
      '#type' => 'textarea',
      '#title' => t('Message to send to the eLetter author on eLetter publication'),
      '#required' => TRUE,
      '#description' => t('Response text a configurable parameter that goes in the body of the published notification email'),
      '#default_value' => !empty($config->get('eLetter_publication_notification_eletterauthor_response_text')) ? $config->get('eLetter_publication_notification_eletterauthor_response_text') : $eletterauthor_response_text,
    );

    $from_address_to_articleauthor = $site_mail;
    $form['eLetter_publication_notification']['eLetter_publication_notification_from_address_to_articleauthor'] = array(
      '#type' => 'textarea',
      '#title' => t('E-mail to send the emails from to Article Author'),
      '#required' => TRUE,
      '#description' => t('From address a configurable parameter, to send a published notification from this to article author'),
      '#element_validate' => array('highwire_responses_moderation_email_validate'),
      '#default_value' => !empty($config->get('eLetter_publication_notification_from_address_to_articleauthor')) ? $config->get('eLetter_publication_notification_from_address_to_articleauthor') : $from_address_to_articleauthor,
    );

    $subject_to_articleauthor = 'A Response regarding your article has been published';
    $form['eLetter_publication_notification']['eLetter_publication_notification_subject_to_articleauthor'] = array(
      '#type' => 'textfield',
      '#title' => t('Subject line for the notification to Article Author'),
      '#required' => TRUE,
      '#description' => t('Email subject line a configurable parameter for the published notification alert email'),
      '#default_value' => !empty($config->get('eLetter_publication_notification_subject_to_articleauthor')) ? $config->get('eLetter_publication_notification_subject_to_articleauthor') : $subject_to_articleauthor,
    );

    $articleauthor_response_text = '<p>Dear !article_author_firstname_lastname,</p>

    <p>An eLetter has been published on [node:field-highwire-c-response-to:field-highwire-a-journal:title]\'s web site. To view it, navigate to your article and click on "eLetter" tab or click the link below. </p>

    <p>eLetter: "[node:title]"</p>

    <p>eLetter URL: [node:field-highwire-c-response-to:url:absolute] </p>

    <p>Sincerely,</p>
    <p>The Editorial Staff of [node:field-highwire-c-response-to:field-highwire-a-journal:title]</p>';
    
    $form['eLetter_publication_notification']['eLetter_publication_notification_articleauthor_response_text'] = array(
      '#type' => 'textarea',
      '#title' => t('Message to send to the Article author on eLetter publication'),
      '#required' => TRUE,
      '#description' => t('Response text a configurable parameter that goes in the body of the published notification email'),
      '#default_value' => !empty($config->get('eLetter_publication_notification_articleauthor_response_text')) ? $config->get('eLetter_publication_notification_articleauthor_response_text') : $articleauthor_response_text,
    );

    $distribution_email_list = $site_mail;
    $form['eLetter_publication_notification']['eLetter_publication_notification_distribution_email_list'] = array(
      '#type' => 'textarea',
      '#title' => t('Moderator Distribution Email'),
      '#required' => TRUE,
      '#description' => t('The moderator distribution list is created externally to the journal site. The moderator is a publisher employee who manages which eLetters get published, and which do not. (If multiple emails required, please enter one email per line)'),
      '#default_value' => !empty($config->get('eLetter_publication_notification_distribution_email_list')) ? $config->get('eLetter_publication_notification_distribution_email_list') : $distribution_email_list,
    );

    // Notification for article author.
    $form['author_notification'] = array(
      '#title' => t("Notification for Article's authors"),
      '#type' => 'fieldset',
    );

    $form['author_notification']['show_set_to_wait_status'] = array(
      '#title' => t('Show "Set eLetter to wait status"'),
      '#type' => 'checkbox',
      '#default_value' => !empty($config->get('show_set_to_wait_status')) ? $config->get('show_set_to_wait_status') : 0,
      '#description' => t('If selected then moderation page will have "Set eLetter to wait status" button, otherwise no'),
    );

    $form['author_notification']['hw_authors_email_from'] = array(
      '#title' => t('E-mail address: From'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#default_value' => !empty($config->get('hw_authors_email_from')) ? $config->get('hw_authors_email_from') : $site_mail,
    );

    $email_subject = 'CMAJ response: "!eletter_title"';
    $form['author_notification']['author_notification_email_subject'] = array(
      '#title' => t('Email subject'),
      '#type' => 'textfield',
      '#default_value' => !empty($config->get('author_notification_email_subject')) ? $config->get('author_notification_email_subject') : $email_subject,
    );

    // Email body.
    $defaut_value = '<p>Dear Author,</p>
    <p>An eLetter was submitted to !journal_name for your article. It is our intention to post this letter on<br />
      the web site and we would like to invite your feedback on the eLetter.</p>
    <p>Your article (citation):</p>
    <p>!article_citation</p>
    <p>!article_link</p>
    <p>The eLetter was submitted on !submitted_date:</p>
    <p>!eletter_citation</p>
    <p>&nbsp;</p>
    <p>If you would like to submit a response to this eLetter, please click on the &quot;Submit a Response to This Article&quot; link in the eLetter tab of your article.<br />
      Please let us know if you have any other questions or reactions to this process.</p>
    <p>Sincerely,<br />
      The Editorial staff of !journal_name</p>';
    
    $email_body = !empty($config->get('author_notification_email_body')) ? $config->get('author_notification_email_body') : $defaut_value;
    $form['author_notification']['author_notification_email_body'] = array(
      '#title' => t('Email body'),
      '#type' => 'text_format',
      '#default_value' => !empty($email_body['value']) ? $email_body['value'] : '',
      '#format' => !empty($email_body['format']) ? $email_body['format'] : '',
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
      ->set('highwire_rr_email_sender', $form_state->getValue('highwire_rr_email_sender'))
      ->set('highwire_rr_email_subject', $form_state->getValue('highwire_rr_email_subject'))
      ->set('highwire_rr_email_response_sent', $form_state->getValue('highwire_rr_email_response_sent'))
      ->set('highwire_e_letter_submission_message', $form_state->getValue('highwire_e_letter_submission_message'))
      ->set('moderator_distribution_email_list', $form_state->getValue('moderator_distribution_email_list'))
      ->set('moderator_site_feedback_email_list', $form_state->getValue('moderator_site_feedback_email_list'))
      ->set('moderator_notification_from_address', $form_state->getValue('moderator_notification_from_address'))
      ->set('moderator_notification_to_address', $form_state->getValue('moderator_notification_to_address'))
      ->set('moderator_notification_email_subject', $form_state->getValue('moderator_notification_email_subject'))
      ->set('moderator_notification_response_text', $form_state->getValue('moderator_notification_response_text'))
      ->set('eletter_rule_action_flag', $form_state->getValue('eletter_rule_action_flag'))
      ->set('eLetter_publication_notification_from_address_to_eletterauthor', $form_state->getValue('eLetter_publication_notification_from_address_to_eletterauthor'))
      ->set('eLetter_publication_notification_subject_to_eletterauthor', $form_state->getValue('eLetter_publication_notification_subject_to_eletterauthor'))
      ->set('eLetter_publication_notification_eletterauthor_response_text', $form_state->getValue('eLetter_publication_notification_eletterauthor_response_text'))
      ->set('eLetter_publication_notification_from_address_to_articleauthor', $form_state->getValue('eLetter_publication_notification_from_address_to_articleauthor'))
      ->set('eLetter_publication_notification_subject_to_articleauthor', $form_state->getValue('eLetter_publication_notification_subject_to_articleauthor'))
      ->set('eLetter_publication_notification_articleauthor_response_text', $form_state->getValue('eLetter_publication_notification_articleauthor_response_text'))
      ->set('eLetter_publication_notification_distribution_email_list', $form_state->getValue('eLetter_publication_notification_distribution_email_list'))
      ->set('show_set_to_wait_status', $form_state->getValue('show_set_to_wait_status'))
      ->set('hw_authors_email_from', $form_state->getValue('hw_authors_email_from'))
      ->set('author_notification_email_subject', $form_state->getValue('author_notification_email_subject'))
      ->set('author_notification_email_body', $form_state->getValue('author_notification_email_body'))
      ->save();
  }
}
