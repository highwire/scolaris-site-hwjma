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
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\highwire_pap\Form\PapConfigForm;

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
		$author_names[$key] = $value['value'];
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
  $emailArticle = $variables['elements']['email_article'];
  $variables['hwjma_citations_download'] = $highwire_citations_download;
  $variables['addtoany'] = $addtoany;
	$variables['ccc_permissions_link'] = $ccc_permissions_link;
  $variables['hw_marker_sign_up'] = $hw_marker_sign_up;
  $variables['hw_alerts_sign_up'] = $hw_alerts_sign_up;
  $variables['hw_tag_sign_up'] = $hw_tag_sign_up;
  $variables['emailArticle'] = $emailArticle;
  if (!empty($pdf_summary[0]['uri'])) {
    $variables['variant_full_text_pdf'] = '/' . str_replace('sass://', 'content/', $pdf_summary[0]['uri']);
  }
}


/**
 * Implements hook_preprocess_node().
 */
function hwjma_preprocess_node__journal_article__toc_list(array &$variables) {
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
  $doi = !$node->get('doi')->isEmpty() ? 'https://doi.org/' .$node->get('doi')->getString() : '';
  if (!empty($node->date_ppub->getValue())) {
    $date_published = $node->date_ppub->getValue()[0]['original'];     
  } 
  else {
   $date_published = $node->date_epub_version->getValue()[0]['original'];     
  }
  $date_time = new \DateTime($date_published);
  $drupal_date_time = DrupalDateTime::createFromDateTime($date_time);
  $pub_date = $drupal_date_time->format('M d, Y');
  $journal_article_citation = $journal_title .' '. $pub_date.' ' . $volume . '(' . $issue_number . '): ' . $first_page . '-' . $last_page.'; DOI: '.$doi;
  $node_journal = \Drupal::routeMatch()->getParameter('node');
  if (!empty($node_journal)) {
    if ($node_journal->hasField('children') && $node_journal->bundle() == "journal") {
      $children = !$node_journal->get('children')->isEmpty() ? $node_journal->get('children')->getValue() : '';
      if (isset($children) && count($children) == 1) {
        $journal_article_citation = $journal_title .' '. $pub_date. ' ' . $issue_number . ': ' . $first_page . '-' . $last_page.'; DOI: '.$doi;
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
              $journal_article_citation = $journal_title .' '. $pub_date. ' ' . $issue_number . ': ' . $first_page . '-' . $last_page.'; DOI: '.$doi;
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
          $journal_article_citation = $journal_title .' '. $pub_date.' ' . $issue_number . ': ' . $first_page . '-' . $last_page.'; DOI: '.$doi;
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

  $view_content_link_url = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'.$node->id());;
  array_push($action_links, ['href' => $view_content_link_url,'class' => 'article-section__view-article-btn','text' => 'View Article']);
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

  $corpus = $node->get('corpus')->getString();
  $pab_path = \Drupal::config('highwire_pap.config')->get('landing_page_path');
  // Add PAP button in journal page
  if (!empty($pab_path)) {
    $pab_path = str_replace("{corpus_node}", $corpus, $pab_path );
    $variables['pap_tabs']['#text'] = $variables['elements']['pap_alias']['#value'];
    $variables['pap_tabs']['#url'] = $pab_path;
  }

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
  $variables['journal_coverimage'] = '/themes/highwire/hwjma_theme/dist/app-drupal/assets/images/250X350.png';
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

   //JCOREX-96:Latest issue details add Journal page 
    $journal_current_issue = $node->get('journal_current_issue')->getValue()[0]['target_id'];
    if (!empty($journal_current_issue)) {
    $latest_issue_node = Node::load($journal_current_issue);
    $volume_no = $latest_issue_node->get('volume')->getString();
    $issue_no = $latest_issue_node->get('issue')->getString();
    $imagefield = $latest_issue_node->get('variant_cover_image')->getValue();
    $media_url = '';
    if (!empty($imagefield)) {
      $fileid = $imagefield[0]['target_id'];
      if (!empty($fileid)) {
        $file_issue = File::load($fileid);
        if (!empty($file)) {
          $path = $file_issue->getFileUri();
          $media_url = file_create_url($path);
        }
      }
    }

    $date_ppub = $latest_issue_node->get('date_ppub')->getValue()[0]['value'];
    $custom_datetime = '';
    if(!empty($date_ppub)){
      $date_time = new \DateTime($date_ppub);
      $drupal_date_time = DrupalDateTime::createFromDateTime($date_time);
      $custom_datetime = $drupal_date_time->format('F, d M Y');
    }
    $download_pdf_links = $issue_pdf_access = '';
    $apath = $node->hasField('apath') && !$node->get('apath')->isEmpty() ? $node->get('apath')->getString() : '';
    if (!empty($apath)) {
    	$ac_rule_fulltext_pdf = \Drupal::entityTypeManager()->getStorage('access_control_rule')->load('variant_full_text_pdf');
    	$user_access_issue_pdf = $ac_rule_fulltext_pdf->userHasAccess($apath);
    	$issue_pdf_access = $user_access_issue_pdf[$apath];
	$pdf_link = $latest_issue_node->get('enclosures')->getValue();
  	if (basename($pdf_link[0]['uri']) == 'complete-issue.pdf') {
    		$download_pdf_links = file_create_url($pdf_link[0]['uri']);
  	}
        if (!$issue_pdf_access) {
            $variables['issue_pdf_popover_link'] = [
        	'#type' => 'highwire_popover',
        	'#trigger_type' => 'link',
        	'#trigger_attributes' => ['class' => ['inaccessible-pdf-issue downlaod-action']],
        	'#trigger_title' => "Download issue PDF",
        	'#popover_options' => [
         		'placement' => 'bottom',
          		'trigger' => ['mouseenter', 'focus'],
          		'interactive' => TRUE,
          		'allowHTML' => TRUE,
        	],
        	'content' => ['#markup' => "To download content, you need to upgrade your trial to full subscription. Please contact your account manager to do this."],
      	    ];
        }
    }
    $variables['latest_issue_detail'] = [
       'img_src'=> $media_url,
       'volume'=> $volume_no,
       'issue_no'=> $issue_no,
       'date'=> $custom_datetime,
       'download_link'=> $download_link,
       'issue_pdf_access'=> $issue_pdf_access,
       'download_pdf_links' => $download_pdf_links,
    ];
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

/**
 * Implements hook_preprocess_node().
 */
function hwjma_preprocess_node(array &$variables) {
  // Default to turning off byline/submitted.
  $variables['display_submitted'] = FALSE;
  $node = \Drupal::routeMatch()->getParameter('node');
  if ($node instanceof \Drupal\node\NodeInterface) {
    $typeName = $node->bundle();

    //get the current node type
    $variables['content_type'] =  $typeName;

    //check if the page belongs to content type journal article
    if ($typeName == 'journal_article') {
      $nid = $node->id();
      $options = ['absolute' => TRUE];
      $url_object = Url::fromRoute('entity.node.canonical', ['node' => $nid], $options);
      $article_url = $url_object->toString();
      $parent_journal = $node->get('parent_journal')->getValue();
      $parent_issue = $node->get('parent_issue')->getValue();
      $parent_issue_nid = $parent_issue[0]['target_id'];
      $parent_issue_url = null;
      if (!empty($parent_issue)) {
        $node = Node::load($parent_issue_nid);
        $url_object = Url::fromRoute('entity.node.canonical', ['node' => $parent_issue_nid], $options);
        $parent_issue_url = $url_object->toString();
      }

      //get parent journal nid
      $parent_journal_nid = $parent_journal[0]['target_id'];
      $issue = $node->get('issue')->getValue();
      $issue = $issue[0]['value'];
      $volume = !$node->get('volume')->isEmpty() ? $node->get('volume')->getString() : '';
      //load parent journal
      if (!empty($parent_journal_nid)) {
        $node = Node::load($parent_journal_nid);
        if ($node->hasField('children')) {
          $children =  !$node->get('children')->isEmpty() ? $node->get('children')->getValue() : '';
        }
        // Get the total number of child volume for a Periodical
        $num_of_children = count($children);
        $url_object = Url::fromRoute('entity.node.canonical', ['node' => $parent_journal_nid], $options);
        $nid_journal_info = Drupal::entityQuery('node')->condition('type','journal_info')->condition('field_journal',$parent_journal_nid, '=')->execute();
        $cover_image = null;
        foreach ($nid_journal_info as $nid) {
          $node = Node::load($nid);
          $parent_journal_img = $node->get('field_journal_cover_image')->getValue();
          $image_tid = $parent_journal_img[0]['target_id'];
          if (!empty($image_tid)) {
            $file = File::load($image_tid);
            $path = $file->getFileUri();
            $cover_image = file_create_url($path);
          } else {
            $cover_image = '/themes/highwire/hwjma_theme/dist/app-drupal/assets/images/128X128.png';
          }
        }
      }
      // The Periodical that have only 1 volume should not display the volume numbers
      if ($num_of_children == 1) {
        $vol_issue = "Issue ".$issue;
      } else {
        $vol_issue = "Vol ".$volume." Issue ".$issue;
      }

			$periodical_url = $url_object->toString();
			$title = $node->title->value;
      $html_entity_decoded_title =  html_entity_decode($title);
      //set variable
      $variables['periodical_items'] = [
        'periodical_url'=> $periodical_url,
        'cover_img' => $cover_image,
        'title'=> $html_entity_decoded_title ,
        'article_url'=> $parent_issue_url,
        'vol_issue'=> $vol_issue
      ];
    }
  }
}


/*
 * Implements hook_preprocess_node().
*/
function hwjma_preprocess_node__journal_issue__content_details(&$variables) {
  $node = $variables['node'];

  // code to get the  download isue pdf button link.
  $pdf_link = $node->get('enclosures')->getValue();
  if (basename($pdf_link[0]['uri']) == 'complete-issue.pdf') {
    $download_pdf_links = file_create_url($pdf_link[0]['uri']);
  }

  //set variable
  $node_title = $variables['node']->getTitle();
  $volume = $node->get('volume')->getString();
  $date_ppub = $node->get('date_ppub')->getValue()[0]['value'];
  $issue = $node->get('issue')->getString();
  $doi = $node->get('doi')->getString();
  $imagefield = $node->get('variant_cover_image')->getValue();
  $media_url = '/themes/highwire/hwjma_theme/dist/app-drupal/assets/images/250X350.png';
  $media_url_alt='Card Images';
  if (!empty($imagefield)) {
    $img_alt = $node->variant_cover_image->alt;
    $img_title = empty($img_alt) ? $node->variant_cover_image->title : $img_alt;
    $fileid = $imagefield[0]['target_id'];
    if (!empty($fileid)) {
      $file = File::load($fileid);
        if (!empty($file)) {
          $path = $file->getFileUri();
          $media_url = file_create_url($path);
          $media_url_alt = empty($img_title) ? 'Card Images' : $img_title;
        }
    }
  }

  // get parent_journal value
  $parent_journal = $node->get('parent_journal')->getValue();
  $parent_journal = $parent_journal[0]['target_id'];
  if (!empty( $parent_journal)) {
    $journal_node = Node::load($parent_journal);
    $eissn = $journal_node->get('journal_eissn')->getString();
    $pissn = $journal_node->get('journal_pissn')->getString();
    $node_title = $journal_node->title->value;
    if ($journal_node->hasField('children')) {
      $children = !$journal_node->get('children')->isEmpty() ? $journal_node->get('children')->getValue() : '';
    }
    // Get the total number of child volume for a Periodical
    $num_of_children = count($children);

    // Get url of parent periodical
    $options = ['absolute' => TRUE];
    $periodical_url_object = Url::fromRoute('entity.node.canonical', ['node' => $parent_journal], $options);
    $periodical_url = $periodical_url_object->toString();
  }
  $info_nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'journal_info', 'field_journal' => $parent_journal]);
  if (!empty($info_nodes)) {
    $journal_info = reset($info_nodes);
    $journal_society = $journal_info->get('field_journal_society')->getString();
    if (!empty($journal_society)) {
      $node_society = \Drupal::entityTypeManager()->getStorage('node')->load($journal_society);
      $imagefield = $node_society->get('field_society_log')->getValue();
      if (!empty($imagefield)) {
        $img_alt = $node_society->field_society_log->alt;
        $img_title = $node_society->field_society_log->title;
        $fileid = $imagefield[0]['target_id'];
        if (!empty($fileid)) {
          $journalissue_society_imgalt = !empty($img_title) ? $img_title : $img_alt;
          $file = File::load($fileid);
            if (!empty($file)) {
              $path = $file->getFileUri();
              $societymedia_url = file_create_url($path);
              $variables['society'] = ['src'=> $societymedia_url,
                'alt'=> $journalissue_society_imgalt,
                'title'=> $journalissue_society_imgalt,
                'class'=> 'article__top_left-card-img'
              ];
            }
        }
      }
    }
  }

  $custom_datetime = '';
  if(!empty($date_ppub)){
    $date_time = new \DateTime($date_ppub);
    $drupal_date_time = DrupalDateTime::createFromDateTime($date_time);
    $custom_datetime = $drupal_date_time->format('F, Y');
  }

  if (!$node->hasField('custom_meta') || $node->get('custom_meta')->isEmpty()) {
    $custom_meta_items = $node->get('custom_meta')->getValue();

    // Get the custom meta value by name.
    foreach ($custom_meta_items as $item) {
      if ($item['first'] == 'cover-date') {
        $custom_datetime = $item['second'];
      }
    }
  }
  
  // The Periodical that have only 1 volume should not display the volume numbers
  $vol_issue = "Volume ".$volume.", Issue ".$issue." - ".$custom_datetime; 

   // creating variables for node template
   $variables['node_title'] = $node_title;
   $variables['download_pdf_links'] = $download_pdf_links;
   $variables['Journalissue'] = [
     'eissn'=> $eissn,
     'pissn'=> $pissn,
     'date_ppub'=> $date_ppub ,
     'doi'=> $doi,
     'variant_coverimage01'=> $media_url,
     'variant_coverimage01_alt'=> $media_url_alt,
     'vol_issue' => $vol_issue,
     'periodical_url' => $periodical_url,
   ];
   $variables['addtoany'] = $variables['elements']['addtoany'];
   $variables['hw_marker_sign_up'] = $variables['elements']['hw_marker_sign_up'];
   $variables['hw_alerts_sign_up'] = $variables['elements']['hw_alerts_sign_up'];
  $variables['main_heading_with_link'] = [
    'title' => $node_title,
    'href' => $periodical_url,
    'text' => $node_title,
  ];
}

function hwjma_preprocess_node__journal__browse_list_item(&$variables) {
  $node = $variables['node'];
  $type = $node->getType();
  if ($type == 'journal') {
    $info_nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
      'type' => 'journal_info',
      'field_journal' => $node->id(),
    ]);
    if (!empty($info_nodes)) {
      $journal_info = reset($info_nodes);
      $variables['journal_description'] =  html_entity_decode(strip_tags($journal_info->get('field_journal_description' )->getvalue()[0]['value']));
      $variables['journal_editors'] =  ($journal_info->get('field_journal_editors')->getvalue()[0]['value'] ) ;
    }
  }
  $variables['journal'] = [];
  $title =  !$node->get('title')->isEmpty() ? $node->get('title')->getString() : '';
  $html_entity_decoded_title =  html_entity_decode($title);
  $variables['html_entity_decoded_title'] = $html_entity_decoded_title;
  $variables['journal'] += hwjma_get_node_fields($variables, ['journal_current_issue' => 'coverimg']);
  $variables['journal'] += hwjma_get_node_pseudo_fields($variables, ['editors' => 'editors', 'group_description' => 'description', 'group_action_tools' => 'actionTools']);
  $variables['journal'] += hwjma_get_node_fields_as_string($variables, ['journal_pissn' => 'pissn', 'journal_eissn' => 'eissn']);
  $current_issue_link = $variables['elements']['current_issue_link'];
  $variables['current_issue_link'] = $current_issue_link;
  // Add placeholder cover if current issue is missing.
  if (empty($variables['journal']['coverimg']) && !empty($variables['cover_placeholder'])) {
    $plain_title = strip_tags($variables['node']->getTitle());
    $variables['cover_placeholder']['#sr_text'] = t('Go to @title', ['@title' => $plain_title]);
    $variables['journal']['coverimg'] = $variables['cover_placeholder'];
  }
  $cover_image = $variables['elements']['cover_image']['0']['#item'];
  if ($cover_image != null) {
    $image_tid = $cover_image->get('target_id')->getValue();
    if (!empty($image_tid)) {
      $file = File::load($image_tid);
      $path = $file->getFileUri();
      $cover_image_uri =  file_create_url($path);
      $image_alt = $cover_image->alt;
      $image_title = $cover_image->title;
      $image_width = '150';
      $image_height = '210';
    } else {
      $cover_image_uri = '/themes/highwire/hwjma_theme/dist/app-drupal/assets/images/250X350.png';
      $image_alt = 'journal cover image';
      $image_title = 'journal cover image';
      $image_width = '150';
      $image_height = '210';
    }
    $variables['cover_image_uri'] = $cover_image_uri;
    $variables['cover_image_alt'] = $image_alt;
    $variables['cover_image_title'] = $image_title;
    $variables['cover_image_width'] = $image_width;
    $variables['cover_image_height'] = $image_height;
    $corpus = $node->get('corpus')->getString();
    $pab_path = \Drupal::config('highwire_pap.config')->get('landing_page_path');
    // Add PAP button in journal List page
    if (!empty($pab_path)) {
      $pab_path = str_replace("{corpus_node}", $corpus, $pab_path );
      $variables['pap_tabs_link'] = $pab_path;
    }
  }
}

/**
 * Helper function to get node fields for patternlab.
 *
 * @param Array $variables
 *   A variables array from a theme preprocess function.
 * @param Array $fields
 *   An array of patternlab fields to return, keyed by the corresponding drupal field name.
 *
 * @return Array
 *   A render array
 */
function hwjma_get_node_fields($variables, $fields) {
  $render = [];
  if (empty($variables['node']) || empty($variables['content'])) {
    return $render;
  }
  foreach ($fields as $drupal => $pl) {
    if ($variables['node']->hasField($drupal) && !$variables['node']->get($drupal)->isEmpty() && !empty($variables['content'][$drupal])) {
      $render[$pl] = $variables['content'][$drupal];
    }
  }
  return $render;
}

function hwjma_get_node_pseudo_fields($variables, $fields) {
  $render = [];
  if (empty($variables['node']) || empty($variables['content'])) {
    return $render;
  }
  foreach ($fields as $drupal => $pl) {
    if (!empty($variables['content'][$drupal])) {
      $render[$pl] = $variables['content'][$drupal];
    }
  }
  // Make group description toggle id to unique.
  $hw_html_toggle_id = Html::getUniqueId('description--' . $variables['node']->id());
  $render['description']['#attributes']['id'] = $hw_html_toggle_id;
  $render['actionTools']['group_description_toggle']['#attributes']['data-target'] = '#' . $hw_html_toggle_id;
  $render['actionTools']['group_description_toggle']['#attributes']['aria-controls'] = $hw_html_toggle_id;
  return $render;
}

/**
 * Helper function to get node fields as a string for patternlab.
 *
 * @param Array $variables
 *   A variables array from a theme preprocess function.
 * @param Array $fields
 *   An array of patternlab fields to return, keyed by the corresponding drupal field name.
 *
 * @return Array
 *   An array of node field values keyed by field name.
 */
function hwjma_get_node_fields_as_string($variables, $fields) {
  $return = [];
  if (empty($variables['node'])) {
    return $return;
  }
  foreach ($fields as $drupal => $pl) {
    if ($variables['node']->hasField($drupal) && !$variables['node']->get($drupal)->isEmpty()) {
      $return[$pl] = $variables['node']->get($drupal)->getString();
    }
  }
  return $return;
}

function hwjma_preprocess_node__journal_issue__issue_list_item(&$variables) {
  $entity = $variables['node'];
  $custom_meta = '';

  if (!$entity->hasField('custom_meta') || $entity->get('custom_meta')->isEmpty()) {
    $variables['cover_date'] = $custom_meta;
  }
  $custom_meta_items = $entity->get('custom_meta')->getValue();

  // Get the custom meta value by name.
  foreach ($custom_meta_items as $item) {
    if ($item['first'] == 'cover-date') {
      $custom_meta = $item['second'];
    }
  }

  $variables['cover_date'] = $custom_meta;
}

/**
 * Implements hook_preprocess__THEME().
 */
function hwjma_preprocess_node__search_result(&$variables) {
  $node = $variables['node'];
  if ($node !== NULL) {
    $variables = search_result($variables, $node);
  }
}

/**
 * Function for search result
 */
function search_result($variables, $node) {
  $content = $variables['content'];

  $node_type = $node->getType();
  $result = [];

  // Add access icon placeholder.
  $access_icon_placeholder = [
    '#type' => 'access_icon_placeholder',
    '#apath' => $node->get('apath')->getString(),
    '#context' => $node_type . ':search_result',
    '#access_icon' => 'hwjma_access_icon',
  ];
  $entity_type = 'node';
  $view_mode = 'search_short_description';
  $short_text = render(\Drupal::entityTypeManager()->getViewBuilder($entity_type)->view($node, $view_mode));

  $access_icon_render = \Drupal::service('renderer')->render($access_icon_placeholder);
  $variables['access_icon_placeholder'] = $access_icon_render;

  // Add item id.
  $result['id'] = $node->id();

  // Add title & Link to content.
  foreach (['label' => 'title', 'url' => 'url'] as $drupal => $pl) {
    if (!empty($variables[$drupal])) {
      $result[$pl] = $variables[$drupal];
    }
  }

  // Add node fields.
  $result += hwjma_get_node_fields($variables, ['subtitle' => 'subTitle']);

  // Add type label
  $result['type'] = hwjma_get_node_type_lable($node_type);

  $title = $node->get('title_plain')->getString() ? $node->get('title_plain')->getString() : '';
  $html_entity_decoded_title =  html_entity_decode($title);
  $result['title'] = $html_entity_decoded_title;
  // Grab node title for img alt attribute.
  $img_alt_text = $node->get('title_plain')->value ? $node->get('title_plain')->value : HighWireContent::processTitle($node->get('title')->value);
  $title_without_html = strip_tags($img_alt_text);
  $result['img_alt_text'] = $title_without_html;
  $result['short_description'] = $short_text;

  // Add fields based on node type.
  $theme_path = base_path() . drupal_get_path('theme', 'hwjma');

  switch ($node_type) {
    case HW_NODE_TYPE_BOOK:
    case HW_NODE_TYPE_REFERENCE:
      $result += hwjma_get_node_fields($variables, ['cover_image' => 'img', 'hwmd_abstract_book' => 'desc']);
      break;
    case in_array($node_type, HW_NODE_TYPE_BOOK_CHUNK):
      $result['img'] = $theme_path . '/images/icon-chapter.svg';
      $result += hwjma_get_node_fields($variables, ['parent_book' => 'source', 'hwmd_abstract_book' => 'desc']);
      break;
    case HW_NODE_TYPE_ARTICLE:
      $result['img'] = $theme_path . '/images/icon-article.svg';
      $result += hwjma_get_node_fields($variables, ['parent_journal' => 'source', 'hwmd_abstract_article_toc' => 'desc']);
      break;
    case HW_NODE_TYPE_CLINICAL_GUIDELINE:
      $result['img'] = $theme_path . '/images/icon-guideline.svg';
      $result += hwjma_get_node_fields($variables, ['parent_book' => 'source', 'hwmd_abstract_book' => 'desc']);
      break;
    case HW_NODE_TYPE_PRESCRIBING_GUIDELINE:
      $result['img'] = $theme_path . '/images/icon-prescribing.svg';
      $result += hwjma_get_node_fields($variables, ['parent_book' => 'source', 'hwmd_abstract_book' => 'desc']);
      break;
    case HW_NODE_TYPE_PATIENT_EDUCATION:
      $result['img'] = $theme_path . '/images/icon-patient-education.svg';
      $result += hwjma_get_node_fields($variables, ['parent_book' => 'source', 'hwmd_abstract_book' => 'desc']);
      break;
    case HW_NODE_TYPE_PROCEDURE:
      $result['img'] = $theme_path . '/images/icon-procedure.svg';
      $result += hwjma_get_node_fields($variables, ['parent_book' => 'source', 'hwmd_abstract_book' => 'desc']);
      break;
    case in_array($node_type, HW_NODE_TYPE_REFERENCE_CHUNK):
      $result['img'] = $theme_path . '/images/icon-reference.svg';
      $result += hwjma_get_node_fields($variables, ['parent_book' => 'source', 'hwmd_abstract_book' => 'desc']);
      break;
    case HW_NODE_TYPE_JOURNAL:
      $result += hwjma_get_node_fields($variables, ['journal_current_issue' => 'img']);
      $result += hwjma_get_node_pseudo_fields($variables, ['field_journal_description' => 'desc']);
      if (!empty($result['img']) && !empty($variables['url'])) {
        $result['img']['#prefix'] = '<a href="' . $variables['url'] . '">';
        $result['img']['#suffix'] = '</a>';
      }
      break;
    default:
      break;
  }

  if ($node_type == HW_NODE_TYPE_ISSUE) {
    $result += hwjma_get_node_fields($variables, ['variant_cover_image' => 'img', 'issue_title' => 'title', 'volume' => 'volume', 'issue' => 'issue',]);
    $result += hwjma_get_node_pseudo_fields($variables, ['description' => 'desc']);
    if (!empty($result['img']) && !empty($variables['url'])) {
      $result['img']['#prefix'] = '<a href="' . $variables['url'] . '">';
      $result['img']['#suffix'] = '</a>';
    }
  }

  // Add cover placeholder for books / journals.
  if ($node_type == HW_NODE_TYPE_BOOK || $node_type == HW_NODE_TYPE_JOURNAL) {
    if (empty($result['img']) && !empty($variables['cover_placeholder'])) {
      if (empty($variables['cover_placeholder']['#url']) && !empty($variables['url'])) {
        $variables['cover_placeholder']['#url'] = $variables['url'];
        $plain_title = strip_tags($variables['node']->getTitle());
        $variables['cover_placeholder']['#sr_text'] = t('Go to @title', ['@title' => $plain_title]);
      }
      $result['img'] = $variables['cover_placeholder'];
    }
  }
  $img_alt = '';
  $img_title = '';
  $media_url = '/themes/highwire/hwjma_theme/dist/app-drupal/assets/images/250X350.png';

  if ($node_type == 'journal_article') {
      $parent_journal = $node->get('parent_journal')->getValue();
      $parent_journal_nid = $parent_journal[0]['target_id'];
      if (!empty($parent_journal_nid)) {
          $node = Node::load($parent_journal_nid);
          $source_title = $node->getTitle();
          $source_url = $node->toUrl()->toString();
          $nid_journal_info = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
            'type' => 'journal_info',
            'field_journal' => $node->id()]);
          foreach ($nid_journal_info as $nid) {
            $node = $nid;
            $parent_journal_img = $node->get('field_journal_cover_image')->getValue();
            $image_tid = $parent_journal_img[0]['target_id'];
            if (!empty($image_tid)) {
              $file = File::load($image_tid);
              $path = $file->getFileUri();
              $media_url = file_create_url($path);
            }
          }
       }
    }

    if ($node_type == 'item_chapter') {
      $nid = $node->id();
      $options = ['absolute' => TRUE];
      $url_object = Url::fromRoute('entity.node.canonical', ['node' => $nid], $options);
      $chapter_url = $url_object->toString();
      $parent = $node->get('parent')->getValue();
      $parent_nid = $parent[0]['target_id'];
      $parent_issue_url = null;
      if (!empty($parent_nid)) {
          $node = Node::load($parent_nid);
          $url_object = Url::fromRoute('entity.node.canonical', ['node' => $parent_nid], $options);
          $parent_url = $url_object->toString();
      }
      $source_title = $node->get('title')->getstring();
      $source_url = $node->toUrl()->toString();
    }

  if($node->hasField('variant_cover_image') && !$node->get('variant_cover_image')->isEmpty()) {
    $imagefield = $node->get('variant_cover_image')->getValue();
    //check if node has image
    if (!empty($imagefield)) {
      $img_alt = $node->cover_image->alt;
      $img_title = $node->cover_image->title;
      $fileid = $imagefield[0]['target_id'];
      if (!empty($fileid)) {
        $file = File::load($fileid);
        if (!empty($file)) {
          $path = $file->getFileUri();
          $media_url = file_create_url($path);
        }
      }
    }
  }
  if($node->hasField('cover_image') && !$node->get('cover_image')->isEmpty()) {
    $imagefield = $node->get('cover_image')->getValue();
    //check if node has image
    if (!empty($imagefield)) {
      $img_alt = $node->cover_image->alt;
      $img_title = $node->cover_image->title;
      $fileid = $imagefield[0]['target_id'];
      if (!empty($fileid)) {
        $file = File::load($fileid);
        if (!empty($file)) {
          $path = $file->getFileUri();
          $media_url = file_create_url($path);
        }
      }
    }
  }
  if ($node_type == 'journal') {
    $node_id = $node->id();
    $info_nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
      'type' => 'journal_info',
      'field_journal' => $node_id
    ]);
    $media_url = '/themes/highwire/hwjma_theme/dist/app-drupal/assets/images/250X350.png';
    if (!empty($info_nodes)) {
      $journal_info = reset($info_nodes);
      if (!$journal_info->get('field_journal_cover_image')->isEmpty()) {
        $node_cover_image = $journal_info->get('field_journal_cover_image')->getValue();
        $image_tid = $node_cover_image[0]['target_id'];
        if (!empty($image_tid)) {
          $file = File::load($image_tid);
          $path = $file->getFileUri();
          $media_url = file_create_url($path);
        }
      }
    }
  }
  $result['img'] = $media_url;
  if ($source_title) {
    $result['source'] = TRUE;
    $result['source_title'] = $source_title;
    $result['source_url'] = $source_url;
  }
  $variables['result'] = $result;
  return $variables;
}

/**
 * @file
 * Functions to support theming node entities in the Particle theme.
 */
function hwjma_get_node_type_lable($key) {
  $typeArry = [
    'journal' => 'Journal',
    'journal_article' => 'Journal Article',
    'journal_fragment' => 'Journal Fragment',
    'journal_issue' => 'Journal Issue',
    'journal_volume' => 'Journal Volume',
    'item_test_review' => 'Test Review',
    'item_monograph' => 'Monograph',
    'media' => 'Media',
    'item_report_guideline' => 'Reports and Guidelines',
    'item_chapter' => 'Chapter',
  ];
  return $typeArry[$key];
}
