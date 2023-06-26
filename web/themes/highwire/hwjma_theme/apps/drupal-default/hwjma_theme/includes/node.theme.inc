<?php

use Drupal\Component\Utility\Html;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\Element;
use Drupal\Core\Template\Attribute;
use Drupal\contextual\Element\ContextualLinks;
use Drupal\facets\Plugin\Block\FacetBlock;
use Drupal\image\Entity\ImageStyle;
use Drupal\Node\NodeInterface;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\highwire_content\HighWireContent;

/**
 * @file
 * Functions to support theming media entities in the Particle theme.
 */

 /*
* Implements hook_preprocess_node().
*/
function hwjma_preprocess_node__journal__content_details(&$variables) {
    $node = $variables['node'];
    $addtoany = $variables['elements']['addtoany'];
    $hw_marker_sign_up = $variables['elements']['hw_marker_sign_up'];
    $hw_alerts_sign_up = $variables['elements']['hw_alerts_sign_up'];
    $sample_issue_link_nid = $node->get('journal_first_issue')->getString();
    if ($sample_issue_link_nid){
      $sample_issue_link = [
        '#type' => 'link',
        '#url' => Url::fromRoute('entity.node.canonical', ['node' => $sample_issue_link_nid], []),
        '#attributes' => ['class' => ['btn', 'btn-primary']],
      ];
      if (!empty($sample_issue_link['#url'])) {
        $sample_issue_link['#title'] = t('Sample issue');
      }
    }
    $current_issue_link = $variables['elements']['current_issue_link'];
    $title = $variables['elements']['title'];
  
    // creating variables for node template
    $variables['addtoany'] = $addtoany;
    $variables['hw_marker_sign_up'] = $hw_marker_sign_up;
    $variables['hw_alerts_sign_up'] = $hw_alerts_sign_up;
    $variables['sample_issue_link'] = $sample_issue_link;
     $variables['current_issue_link'] = $current_issue_link;
    //load journal_info  content type
     $info_nodes = '';//\Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'journal_info', 'field_journal' => $node->id() ]);
    $variables['title'] = $node->get('title')->getString();
    $variables['eissn'] = $node->get('journal_eissn')->getString();
    $variables['pissn'] = $node->get('journal_pissn')->getString();
    $variables['journal_coverimage'] = '/themes/highwire/bps_theme/dist/app-drupal/assets/images/250X350.png';
    //load data from journal_info node
    if (!empty($info_nodes)) {
      $journal_info = reset($info_nodes);
      $variables['journal_description'] = ($journal_info->get('field_journal_description')->getvalue()[0]['value']);
      $variables['journal_editors'] = $journal_info->get('field_journal_editors')->getvalue()[0]['value'];
      if (!$journal_info->get('field_journal_cover_image')->isEmpty()) {
        $node_cover_image = $journal_info->get('field_journal_cover_image')->getValue();
        $image_tid = $node_cover_image[0]['target_id'];
        if (!empty($image_tid)) {
          $file = File::load($image_tid);
          $path = $file->getFileUri();
          $variables['journal_coverimage'] = file_create_url($path);
        }
      }
      //load society field and check if the journal_society is not empty
      $journal_society = $journal_info->get('field_journal_society')->getString();
      if (!empty($journal_society)) {
        $node_society = \Drupal::entityTypeManager()->getStorage('node')->load($journal_society);
        $journal_society_title = $node_society->title->getString();
        if (!$node_society->get('field_society_log')->isEmpty()) {
          $journal_society_image = $node_society->get('field_society_log')->getValue();
          $image_tid = $journal_society_image[0]['target_id'];
          if (!empty($image_tid)) {
            $file = File::load($image_tid);
            $path = $file->getFileUri();
            $journal_society_imageUrl = file_create_url($path);
          }
        }
        $variables['society'] = [
          'src'=> $journal_society_imageUrl,
          'alt'=>  $journal_society_title,
          'title'=> $journal_society_title,
          'class'=> 'article__top_left-card-img'
        ];
      }
    }
  
  }
  