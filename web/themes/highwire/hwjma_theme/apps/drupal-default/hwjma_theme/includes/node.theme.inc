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

/**
 * Implements hook_preprocess_node().
 */
function hwjma_preprocess_node__journal_article__content_details(array &$variables) {

	$node = $variables['node'];

  // Journal Heading variables
	$article_type = $node->get('article_type')->getValue();
	$article_title = $node->get('title_plain')->getValue();
	$variables['main_title'] = ['text' => $article_title['0']['value'], 'class' => 'article__title'];
	$variables['prefix_title'] = ['text' => $article_type['0']['value'], 'class' => 'article__subtitle'];
  $variables['journal_title'] = ['label' => 'Journal Title:', 'text' => $node->get('journal_title')->getValue()['0']['value']];
  
  // Journal author variables
	$authors = $node->get('authors_full_name')->getValue();
	$author_names = [];
	foreach ($authors as $key => $value) {
		$author_names[$key] = ['text'=>$value['value'], 'class'=> 'article__fullname'];
	}
	$variables['authors'] = $author_names;
  
  // Journal description variables
  $doi = $node->get('doi')->getString();
  //New way to display DOI
  if (!empty($doi)) {
    $doi_link = 'https://doi.org/' . $doi;
    $variables['journal_doi'] = ['label' => 'DOI:', 'href' => $doi_link, 'title' => $doi, 'text' => $doi_link, 'target' => '_blank'];
  }

  // Action tools
	$highwire_citations_download = $variables['elements']['highwire_citations_download'];
  $addtoany = $variables['elements']['addtoany'];
  $ccc_permissions_link = $variables['elements']['ccc_permissions_link'];
  
  $hw_marker_sign_up = $variables['elements']['hw_marker_sign_up'];
  $hw_tag_sign_up = $variables['elements']['hw_tag_sign_up'];
  $hw_alerts_sign_up = $variables['elements']['hw_alerts_sign_up'];
  if (!empty($variables['elements']['variant_full_text_pdf']['#items']) && count($variables['elements']['variant_full_text_pdf']['#items']) > 0) {
    $pdf_summary = $variables['elements']['variant_full_text_pdf']['#items']->getValue('uri');
  }
  $variables['hwjma_citations_download'] = $highwire_citations_download;
  $variables['addtoany'] = $addtoany;
	$variables['ccc_permissions_link'] = $ccc_permissions_link;
  $variables['hw_marker_sign_up'] = $hw_marker_sign_up;
  $variables['hw_alerts_sign_up'] = $hw_alerts_sign_up;
  $variables['hw_tag_sign_up'] = $hw_tag_sign_up;
  if (!empty($pdf_summary[0]['uri'])) {
    $variables['variant_full_text_pdf'] = '/' . str_replace('sass://', 'content/', $pdf_summary[0]['uri']);
  }
}


/**
 * Implements hook_preprocess_node().
 */
function hwjma_preprocess_node__journal_article__toc_list_(array &$variables) {
  // Default to turning off byline/submitted.
  $variables['display_submitted'] = FALSE;
  $node = $variables['node'];
  $apath = $node->get('apath')->getValue()['0']['value'];
  $journal_title = !$node->get('journal_title')->isEmpty() ? $node->get('journal_title')->getString() : '';
  $journal_title = htmlspecialchars($journal_title);
  $volume = !$node->get('volume')->isEmpty() ? $node->get('volume')->getString() : '';
  $issue_number = !$node->get('issue')->isEmpty() ? $node->get('issue')->getString() : '';
  $first_page = !$node->get('fpage')->isEmpty() ? $node->get('fpage')->getString() : '';
  $last_page = !$node->get('lpage')->isEmpty() ? $node->get('lpage')->getString() : '';
  $journal_article_citation = $journal_title . ' ' . $volume . '(' . $issue_number . '): ' . $first_page . '-' . $last_page;
  $node_journal = \Drupal::routeMatch()->getParameter('node');
  if (!empty($node_journal)) {
    if ($node_journal->hasField('children') && $node_journal->bundle() == "journal") {
      $children = !$node_journal->get('children')->isEmpty() ? $node_journal->get('children')->getValue() : '';
      if (isset($children) && count($children) == 1) {
        $journal_article_citation = $journal_title . ' ' . $issue_number . ': ' . $first_page . '-' . $last_page;
      }
    }
    elseif ($node_journal->bundle() == "journal_issue") {
        $parent_journal = $node->get('parent_journal')->getString();
        //get parent journal nid
        if ($parent_journal) {
          $node_issue = Node::load($parent_journal);
          if ($node_issue->hasField('children')) {
            $children =  !$node_issue->get('children')->isEmpty() ? $node_issue->get('children')->getValue() : '';
            // Get the total number of child volume for a Periodical
            if (isset($children) && count($children) == 1) {
              $journal_article_citation = $journal_title . ' ' . $issue_number . ': ' . $first_page . '-' . $last_page;
            }
          }
        }
    }
  }
  else {
    // condition applied for most read tab as node_journal is coming empty for most read tab.
    $parent_journal_most_read = $node->get('parent_journal')->getString();
    //get parent journal nid
    if ($parent_journal_most_read) {
      $node_issue = Node::load($parent_journal_most_read);
      if ($node_issue->hasField('children')) {
        $children = !$node_issue->get('children')->isEmpty() ? $node_issue->get('children')->getValue() : '';
        // Get the total number of child volume for a Periodical
        if (isset($children) && count($children) == 1) {
          $journal_article_citation = $journal_title . ' ' . $issue_number . ': ' . $first_page . '-' . $last_page;
        }
      }
    }
  }
  // Fetching author names according to pattern lab array
  $authors = $node->get('authors_full_name')->getValue();
  $author_names = [];
  if (!empty($authors)) {
    foreach ($authors as $key => $value) {
      if ($key != (count($authors)-1)) {
        $author_names[$key] = ['paragraph' => ['text'=>$value['value'], 'class'=> 'article-section__author-name']];
      } else {
        $author_names[$key] = ['paragraph' => ['text'=>$value['value'], 'class'=> 'article-section__author-sub']];
      }
    }
  }
  array_push($author_names,['paragraph' => ['text'=> $journal_article_citation, 'class'=> 'article-section__author-description']]);
  $variables['content']['author_names'] = $author_names;

  // Fetching show abstract according to pattern lab array
  $action_links = [];
  $hwmd_abstract = $variables['content']['hwmd_abstract_article_toc_'];
  $abstract_markup = $hwmd_abstract['0']['markup'][$apath]['content']['#markup'];
  if ($abstract_markup != null ) {
    array_push($action_links, ['href' => '#','class' => 'article-section__abstract-btn show-click-toggle','text' => 'Show abstract']);
    $variables['content']['article_abstract'] = $abstract_markup;
  }

  // Fetching view content links according to pattern lab array
  $view_content_link = $variables['content']['view_content_link'];
  $view_content_link_url = $view_content_link['#url'];
  array_push($action_links, ['href' => $view_content_link_url->toString(),'class' => 'article-section__view-article-btn','text' => $view_content_link['#title']]);
  if (!empty($variables['elements']['variant_full_text_pdf']['#items']) && count($variables['elements']['variant_full_text_pdf']['#items']) > 0) {
    $variant_full_text_pdf = $variables['elements']['variant_full_text_pdf']['#items']->getValue('uri');
  } else {
    $variant_full_text_pdf['0']['uri'] = '#';
  }
  if (!empty($variant_full_text_pdf[0]['uri'])) {
    $variant_full_text_pdf_url  = '/' . str_replace('sass://', 'content/', $variant_full_text_pdf[0]['uri']);
  } else {
    $variant_full_text_pdf_url = '#';
  }
  $variables['content']['action_links'] = $action_links;

  // code written to parse the title to avoid issue mentioned in hwjmaSCOL-736
  $variables['label'] = html_entity_decode($node->get('title_plain')->getString());
}


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
   $info_nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'journal_info', 'field_journal' => $node->id() ]);
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