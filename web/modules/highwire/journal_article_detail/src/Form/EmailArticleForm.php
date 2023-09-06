<?php

namespace Drupal\journal_article_detail\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Drupal\Core\Routing;
use Drupal\Core\Ajax\HtmlCommand;

/**
 * Provides the form for adding countries.
 * Use for Email Article frontend form : JCOREX-343
 */
class EmailArticleForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'email_article_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $node = NULL) {
    $article_title = '';
    $alias = '';
    if (is_object($node)) {
      $article_title = $node->get('title')->getString();
      $nid = $node->id();
      $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'.$nid);
    }

    // Get email article configuration data
    $emailArticleConfig = \Drupal::config('journal_article_detail.settings');
    $thanksMsgDisplay = $emailArticleConfig->get('thanks_msg_display');
    $thanksMsg = $emailArticleConfig->get('thanks_msg');
    $emailArticleNoteDisplay = $emailArticleConfig->get('email_article_note_display');
    $emailArticleNote = $emailArticleConfig->get('email_article_note');
    $messageSubjectDisplay = $emailArticleConfig->get('message_subject_display');
    $messageSubject = $emailArticleConfig->get('message_subject');
    $messageBodyDisplay = $emailArticleConfig->get('message_body_display');
    $messageBody = $emailArticleConfig->get('message_body');
    
    if ($thanksMsgDisplay) {
      $form['thanksMsg'] = [
        '#type' => 'markup',
        '#markup' => $thanksMsg
      ];
    }

    if ($emailArticleNoteDisplay) {
      $form['emailArticleNote'] = [
        '#type' => 'markup',
        '#markup' => $emailArticleNote
      ];
    }

    $form['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your Email <span class="form-item-required" title="This field is required.">*</span>'),
      '#required' => TRUE,
      '#maxlength' => 50,
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your Name <span class="form-item-required" title="This field is required.">*</span>'),
      '#required' => TRUE,
      '#maxlength' => 20,
    ];

    $form['sendto'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Send To <span class="form-item-required" title="This field is required.">*</span>'),
      '#required' => TRUE,
      '#cols' => 50,
      '#rows' => 5
    ];

    $form['articleTitleDiv'] = [
      '#type' => 'markup',
      '#markup' => '<div class="div-text"> <span> You are going to email the following </span><p><a href="'.$alias.'">' .  $article_title . '</a></p></div>'
    ];

    if ($messageSubjectDisplay) {
      $form['messageSubject'] = [
        '#type' => 'markup',
        '#markup' => '<div class="div-text"> <span>  Message Subject </span> <p>' . $messageSubject .' </p> </div>'
      ];
    }

    if ($messageBodyDisplay) {
      $form['messageBody'] = [
        '#type' => 'markup',
        '#markup' => '<div class="div-text"> <span> Message Body </span> <p>' . $messageBody . '</p> </div>'
      ];
    }

    $form['messageText'] = array(
        '#type' => 'textarea',
        '#title' => $this->t('Your Personal Message'),
        '#cols' => 50,
        '#rows' => 5
    );

    $form['captchaLabel'] = [
      '#type' => 'markup',
      '#markup' => '<div class="div-text"> <span> CAPTCHA </span> <p> This question is for testing whether or not you are a human visitor and to prevent automated spam submissions. </p> </div>'
    ];
	
    $form['captcha'] = array(
      '#type' => 'captcha',
      '#captcha_type' => 'image_captcha/Image',
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#default_value' => $this->t('Send Message'),
      '#attributes' => ['class' => ['btn btn-primary']],
    ];

    return $form;
  }
  
   /**
   * {@inheritdoc}
   */
  public function validateForm(array & $form, FormStateInterface $form_state) {
    // Validation on required fields
    $field = $form_state->getValues();
		$fields["email"] = $field['email'];
		if (!$form_state->getValue('email') || empty($form_state->getValue('email'))) {
        $form_state->setErrorByName('email', $this->t('Provide Email'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Geting form data values
    $field = $form_state->getValues();
		$fields["email"] = [$field['name'] => 'drupal-admin@highwire.org'];
		$fields["name"] = $field['name'];
		$fields["sendto"] = $field['sendto'];
		$fields["message"] = $field['messageText'];

    // Get a drupal mail service
    $mail_manager = \Drupal::service('plugin.manager.mail');
    // Prepare mail params
    $params = [
        'title' => $fields["name"] . ' has sent you a message from HWJMA',
        'message' => $fields["message"],
        'from' => $fields["email"]
    ];
    // Send a mail
    $result = $mail_manager->mail('journal_article_detail',
        'email_article',
        $fields["sendto"],
        \Drupal::currentUser()->getPreferredLangcode(),
        $params,
        NULL,
        TRUE);
    // If mail not send then set error message
    if ($result['result'] != true) {
        $message = t('There was a problem sending your email notification to @email.', array('@email' => $to));
        drupal_set_message($message, 'error');
        return;
    }
    // If mail send then set success message
    $message = t('An email notification has been sent to @email ', array('@email' => $fields["sendto"]));
    drupal_set_message($message);
  }
}
