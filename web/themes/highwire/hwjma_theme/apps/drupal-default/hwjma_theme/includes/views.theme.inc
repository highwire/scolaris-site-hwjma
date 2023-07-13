<?php

use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\entityqueue\Entity\EntitySubqueue;
use \Drupal\Core\Url;

/**
 * @file
 * Functions to support theming media entities in the Particle theme.
 */

function hwjma_preprocess_views_view__homepage_highlights_carousel(&$variables) {
  foreach ($variables['view']->result as $result) {
    $node = $result->_entity;
    $imagefield = $node->get('variant_cover_image')->getValue();
    $media_url = '/themes/highwire/hwjma_theme/dist/app-drupal/assets/images/250X350.png';
    if (!empty($imagefield)) {
      $fileid = $imagefield[0]['target_id'];
      if (!empty($fileid)) {
        $file = File::load($fileid);
          if (!empty($file)) { 
            $path = $file->getFileUri();
            $media_url = file_create_url($path);
          }
      }
    }

    $variables['issue'][$node->id()]['text'] = $node->getTitle();
    $variables['issue'][$node->id()]['image']['alt'] = $node->getTitle();
    $variables['issue'][$node->id()]['image']['title'] = $node->getTitle();
    $variables['issue'][$node->id()]['href'] = $node->toUrl()->toString();
    $variables['issue'][$node->id()]['image']['src'] = $media_url;

    $parent_journal = $node->get('parent_journal')->getValue();
    $parent_journal_nid = $parent_journal[0]['target_id'];
    $issue = !$node->get('issue')->isEmpty() ? $node->get('issue')->getString() : '';
    $volume = !$node->get('volume')->isEmpty() ? $node->get('volume')->getString() : '';
    if (!empty($parent_journal_nid)) {
      $parent_journal_node = Node::load($parent_journal_nid);
      $periodical_title = $parent_journal_node->title->value;
      if ($parent_journal_node->hasField('children')) {
        $children = !$parent_journal_node->get('children')->isEmpty() ? $parent_journal_node->get('children')->getValue() : '';
      }
      // Get the total number of child volume for a Periodical      
      $num_of_children = count($children);
    }
    // The Periodical that have only 1 volume should not display the volume numbers
    if ($num_of_children == 1) {
      $vol_issue = $periodical_title." Issue ".$issue;
    } else {
      $vol_issue = $periodical_title." Vol ".$volume." Issue ".$issue;
    }
    $variables['issue'][$node->id()]['paragraph']['text'] = $vol_issue;
  }
}
