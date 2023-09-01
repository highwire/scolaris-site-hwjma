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
  public function buildForm(array $form, FormStateInterface $form_state, $get_article_title = NULL) {

    $emailArticleConfig = \Drupal::config('journal_article_detail.settings');
    $thanksMsg = $emailArticleConfig->get('thanks_msg');
    $emailArticleNote = $emailArticleConfig->get('email_article_note');
    $messageSubject = $emailArticleConfig->get('message_subject');
    $messageBody = $emailArticleConfig->get('message_body');
    
    $form['thanksMsg'] = [
      '#type' => 'markup',
      '#markup' => $thanksMsg
    ];

    $form['emailArticleNote'] = [
      '#type' => 'markup',
      '#markup' => $emailArticleNote
    ];

    $form['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your Email*'),
      '#required' => TRUE,
      '#maxlength' => 50,
      '#default_value' =>  '',
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your Name*'),
      '#required' => TRUE,
      '#maxlength' => 20,
      '#default_value' =>  '',
    ];

	  $form['sendto'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Send To*'),
      '#required' => TRUE,
      '#maxlength' => 50,
      '#default_value' => '',
    ];

    $form['articleLink'] = [
      '#type' => 'markup',
      '#markup' => '<div> You are going to email the following </div>' . $get_article_title
    ];

    $form['messageSubject'] = [
      '#type' => 'markup',
      '#markup' => '<div> Message Subject </div>' . $messageSubject
    ];

    $form['messageBody'] = [
      '#type' => 'markup',
      '#markup' => '<div> Message Body </div>' . $messageBody
    ];

	$form['messageText'] = array(
        '#type' => 'textarea',
        '#title' => $this->t('Your Personal Message'),
        // '#default_value' => '',
        '#cols' => 50,
        '#rows' => 5
    );
	
    $form['captcha'] = array(
      '#type' => 'captcha',
      '#captcha_type' => 'image_captcha/Image',
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#default_value' => $this->t('Send Message') ,
    ];

    return $form;
  }
  
   /**
   * {@inheritdoc}
   */
  public function validateForm(array & $form, FormStateInterface $form_state) {
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
    $field = $form_state->getValues();
		$fields["email"] = [$field['name'] => 'drupal-admin@highwire.org'];
		$fields["name"] = $field['name'];
		$fields["sendto"] = $field['sendto'];
		$fields["message"] = $field['messageText'];

        $mail_manager = \Drupal::service('plugin.manager.mail');
        $params = [
            'title' => $fields["name"] . ' has sent you a message from HWJMA',
            'message' => $fields["message"],
            'from' => $fields["email"]
          ];
        $result = $mail_manager->mail('journal_article_detail',
            'email_article',
            $fields["sendto"],
            \Drupal::currentUser()->getPreferredLangcode(),
            $params,
            NULL,
            TRUE);
            drupal_set_message('Success');

        if ($result['result'] != true) {
            $message = t('There was a problem sending your email notification to @email.', array('@email' => $to));
            drupal_set_message($message, 'error');
            \Drupal::logger('mail-log')->error($message);
            return;
        }
        $message = t('An email notification has been sent to @email ', array('@email' => $fields["sendto"]));
        drupal_set_message($message);
        \Drupal::logger('mail-log')->notice($message);
  }
}