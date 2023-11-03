<?php

namespace Drupal\journal_article_detail\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Action description.
 *
 * @Action(
 *   id = "eletter_module_held_action",
 *   label = @Translation("Set eLetter to held status"),
 *   type = ""
 * )
 */
class EletterHeldAction extends ViewsBulkOperationsActionBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    // Update workflow status
    if (is_object($entity)) {
      $entity->set('field_workflow_state', 'H');
      $entity->save();
      // Use when author data get from content highwire_responses_moderation_notify_author_and_eletter_author($entity->id());
      return $this->t('Workflow status are updated');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    // If certain fields are updated, access should be checked against them as well.
    // @see Drupal\Core\Field\FieldUpdateActionBase::access().
    return $object->access('update', $account, $return_as_object);
  }
}

/**
 * {@inheritdoc}
 */
function highwire_responses_moderation_notify_author_and_eletter_author($article_nid) {
  // Get config values
  $journalArticleDetailConfig = \Drupal::config('journal_article_detail.settings');
  if (!$journalArticleDetailConfig->get('eletter_rule_action_flag')) {
    return true;
  }
  // Get the configured details for the notification to article author
  $article_author_response_text = $journalArticleDetailConfig->get('eLetter_publication_notification_articleauthor_response_text');
  $article_author_notify_from_address = $journalArticleDetailConfig->get('eLetter_publication_notification_from_address_to_articleauthor');
  $article_author_msg_subject = $journalArticleDetailConfig->get('eLetter_publication_notification_subject_to_articleauthor');

  // Get the configured details for the notification to eLetter author
  $eLetter_author_response_text = $journalArticleDetailConfig->get('eLetter_publication_notification_eletterauthor_response_text');
  $eLetter_author_notify_from_address = $journalArticleDetailConfig->get('eLetter_publication_notification_from_address_to_eletterauthor');
  $eLetter_author_msg_subject = $journalArticleDetailConfig->get('eLetter_publication_notification_subject_to_eletterauthor');
  
  // Get the system configured moderator distribution list
  $publish_notify_moderator_dist_list = $journalArticleDetailConfig->get('eLetter_publication_notification_distribution_email_list');
  
  if (isset($eLetter_author_msg_subject) && isset($publish_notify_moderator_dist_list)) {
    $publish_notify_moderator_dist_email_list_array = explode("\n", $publish_notify_moderator_dist_list);
    $publish_notify_moderator_dist_final_list = '';
    // Check moderator email is empty or not
    if (!empty($publish_notify_moderator_dist_email_list_array)) {
      // Get the size of the array
      $pub_mod_array_size = count($publish_notify_moderator_dist_email_list_array);
      // Creating comma separated values from the array elements to set this for 'CC' header
      for ($k = 0; $k < $pub_mod_array_size; $k++) {
        if ($k == ($pub_mod_array_size - 1)) {
          $publish_notify_moderator_dist_final_list = $publish_notify_moderator_dist_final_list . trim($publish_notify_moderator_dist_email_list_array[$k]);
        }
        else {
          $publish_notify_moderator_dist_final_list = $publish_notify_moderator_dist_final_list . trim($publish_notify_moderator_dist_email_list_array[$k]) . ',';
        }
      }
    }
    
    // Validating if article node id exists
    if (!empty($article_nid)) {
      $article_node = Node::load($article_nid);
      // Get the email address and names from authors/contributors.  $author_to_email_ids $author_to_names author detail not available 
      // Get the email address and names from eLetter authors. $eLetter_author_to_email_ids $eLetter_author_to_names 
      $token_conf = array('node' => $node);
      // Notification to article author on eLetter publishing and Notification to eLetter author on eLetter publishing
      return true;
    }
  }
}
