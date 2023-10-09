<?php

namespace Drupal\journal_article_detail\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Action description.
 *
 * @Action(
 *   id = "eletter_module_release_action",
 *   label = @Translation("Set eLetter to release status"),
 *   type = ""
 * )
 */
class EletterReleaseAction extends ViewsBulkOperationsActionBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    // Update workflow status
    if (is_object($entity)) {
      $entity->set('field_workflow_state', 'R');
      $entity->save();
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
