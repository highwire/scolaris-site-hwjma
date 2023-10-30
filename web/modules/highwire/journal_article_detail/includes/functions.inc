<?php 

use Drupal\node\Entity\Node;
use Drupal\Core\Url;

/**
 * Rules action for notifying moderator.
 */
function highwire_responses_moderation_notify_moderator($form_state, $journalArticleDetailConfig) {
  // Get config values from eLetter configuration
  $site_mail = \Drupal::config('system.site')->get('mail');
  $moderator_dist_email_list_value = $journalArticleDetailConfig->get('moderator_distribution_email_list');
  $moderator_site_feedback_email_address = $journalArticleDetailConfig->get('moderator_site_feedback_email_list');
  $moderator_notification_from_address = $journalArticleDetailConfig->get('moderator_notification_from_address');
  $moderator_notification_to_address = $journalArticleDetailConfig->get('moderator_notification_to_address');
  $moderator_notification_email_subject = $journalArticleDetailConfig->get('moderator_notification_email_subject');
  $moderator_notification_msg = $journalArticleDetailConfig->get('moderator_notification_response_text');
  $moderator_dist_email_list_array = explode("\n", $moderator_dist_email_list_value);

  // Check all variables are set or not
  if (isset($moderator_dist_email_list_value)
    && isset($moderator_site_feedback_email_address)
    && isset($moderator_notification_from_address)
    && isset($moderator_notification_to_address)
    && isset($moderator_notification_email_subject)
    && isset($moderator_notification_msg)) {

    // Retrieve article id from frontend form
    $article_nid = $form_state->getValue('field_highwire_comment_response')[0]['target_id'];
    if (!empty($article_nid)) {
      // Retrieve eLetter response text and title
      $response_text = $form_state->getValue('body')[0]['value'];
      $node = Node::load($article_nid);
      $token_conf = array('node' => $node);

      // Make sure that moderator distribution list is not empty
      if (!empty($moderator_dist_email_list_array)) {
        // Looping through the configured moderator distribution list
        foreach ($moderator_dist_email_list_array as $list_key => $moderator_dist_email_list) {
          // Replacing tokens for 'from' email address
          $tokens_from_address = array(
            '!site_feeback_email_address' => t($moderator_site_feedback_email_address),
          );

          $token_service = \Drupal::token();

          // This string replace is to replace the moderator notification address configured in the backend. Since no corresponding token exists, should be replaced using str_replace only
          $from_notificaton_address = str_replace(array_keys($tokens_from_address), $tokens_from_address, $moderator_notification_from_address);

          // After replacing moderator notification address using str_replace, token_replace used to replace available (or) configured tokens alone
          $from_notificaton_address = $token_service->replace($from_notificaton_address, $token_conf);

          // Replacing tokens for 'to' email address
          $tokens_to_address = array(
            '!moderator_distribution_email_list' => t($moderator_dist_email_list),
          );

          // This string replace is to replace the moderator notification address configured in the backend. Since no corresponding token exists, should be replaced using 'str_replace' only
          $to_notificaton_address = str_replace(array_keys($tokens_to_address), $tokens_to_address, $moderator_notification_to_address);

          // After replacing moderator notification address using str_replace, token_replace used to replace available (or) configured tokens alone
          $to_notificaton_address = $token_service->replace($to_notificaton_address, $token_conf);

          $response_subject = $token_service->replace($moderator_notification_email_subject, $token_conf);

          $site_addr = "http://" . \Drupal::request()->getHost();
          $tokens_msg = array(
            '!article_title' => $node->title->getValue()[0]['value'],
            '!article_link' => $site_addr . '/node/' . $article_nid,
            '!eletter_title' => $form_state->getValue('title')[0]['value'],
            '!eletter_text' => $form_state->getValue('body')[0]['value'],
            '!eLetter_link_copy' => 'node/' . $form_state->getValue('nid'),
          );

          // This string replace is to replace the moderator notification address configured in the backend. Since no corresponding token exists, should be replaced using 'str_replace' only
          $response_body = str_replace(array_keys($tokens_msg), $tokens_msg, $moderator_notification_msg);
          // After replacing moderator notification address using str_replace, token_replace used to replace available (or) configured tokens alone
          $response_body = $token_service->replace($response_body, $token_conf);

          $params = [];
          $params['from'] = $from_notificaton_address;
          $params['body'] = $response_body;
          $params['subject'] = $response_subject;
          // Get a drupal mail service
          $mail_manager = \Drupal::service('plugin.manager.mail');
          if (!empty($to_notificaton_address)) {
            // Send a mail
            $result = $mail_manager->mail('journal_article_detail',
            'highwire_responses_moderation',
            $to_notificaton_address,
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
          }
        }
      }
    }
  }
  else {
    drupal_set_message('The required parameters configurations to trigger notification alert to moderator does not exist!', 'error');
  }
  return true;
}
