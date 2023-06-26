<?php

use Drupal\node\Entity\Node;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Utility\Token;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;

/**
 * Implements hook_preprocess_THEME().
 */
function hwjma_preprocess_block__block_image(&$variables) {
  if (empty($variables['content']['#block_content']) || empty($variables['content']['field_image'][0])) {
    return;
  }

  // Add url to image formatter if it is not empty.
  $block_content = $variables['content']['#block_content'];
  if ($block_content->hasField('field_url') && !$block_content->get('field_url')->isEmpty()) {
    $url_field = $block_content->get('field_url')->first()->getValue();
    if (!empty($url_field['uri'])) {
      $variables['content']['field_image'][0]['#url'] = Url::fromUri($url_field['uri']);
    }
  }

  // Set image_formatter__2x theme hook for retina support.
  if (!empty($variables['content']['field_image'][0]['#item'])) {
    $variables['content']['field_image'][0]['#theme'] = 'image_formatter__2x';
  }
}

/**
 * Implements hook_preprocess_THEME().
 */
function hwjma_preprocess_image_formatter(&$variables) {
  // Add url_attributes.
  if (!empty($variables['url'])) {
    $variables['url_attributes'] = new Attribute([]);

    // Open external links in new window.
    if ($variables['url']->isExternal()) {
      $variables['url_attributes']['target'] = '_blank';
    }
  }
}

/**
 * Implements hook_preprocess_THEME().
 */
function hwjma_preprocess_image_formatter__2x(&$variables) {
  if (empty($variables['item']) || empty($variables['image'])) {
    return;
  }

  // Set width & height to half natural value to support retina display.
  // This requires the source image to be twice as large as it is meant to be displayed.
  // (E.g. to display an image at 100x100 in the browser, upload the image at 200x200.)
  $img_props = $variables['item']->getProperties();
  $width = !empty($img_props['width']) ? $img_props['width']->getValue() : '';
  $height = !empty($img_props['height']) ? $img_props['height']->getValue() : '';
  if (!empty($width)) {
    $variables['image']['#width'] = $width / 2;
  }
  if (!empty($height)) {
    $variables['image']['#height'] = $height / 2;
  }
}

/**
 * Process Table of contents right sidebar block content
 */
function hwjma_preprocess_block__highwire_content_nav_block(&$variables) {
  $toc_sidebar_block['items']['block_heading'] = $variables['label'];
	$contents = $variables['content'];
	foreach ($contents as $k => $v) {
		$items = $v['#items'];
		foreach ($items as $items_key => $item) {
			$item_title = $item['#title']->__toString();
			$title_url = $item['#url']->toString();
			$toc_sidebar_block['items']['listing_items'][$items_key]['title'] = html_entity_decode($item_title);
			$toc_sidebar_block['items']['listing_items'][$items_key]['url'] = $title_url;
		}
	}
	if ($toc_sidebar_block['items']['listing_items'] == NULL) {
		$variables['toc_sidebar_block'] = false;	
	} else {
		$variables['toc_sidebar_block'] = $toc_sidebar_block;
	}	
}


/**
 * Implements hook_preprocess_THEME().
 */
function hwjma_preprocess_node__page__full(&$variables) {
	if (empty($variables['node'])) {
		return;
	}
	// Add left hand sidebar.
	$variables['page_sidebar'] = FALSE;

	// Add subtitle.
	if (!empty($variables['content']['field_page_subtitle']) && !empty($variables['content']['field_page_subtitle']['#theme'])) {
		$variables['page_subtitle'] = $variables['content']['field_page_subtitle'];
		unset($variables['content']['field_page_subtitle']);
	}

	if (!empty($variables['content']['hwjma_cms_navigation'])) {
		$variables['page_sidebar'] = TRUE;
		$variables['cms_nav'] = $variables['content']['hwjma_cms_navigation'];
		unset($variables['content']['hwjma_cms_navigation']);
	}

	// Ensure ID exists for collapse container.
	if (empty($variables['attributes']['id'])) {
		$variables['attributes']['id'] = Html::getUniqueId($variables['node']->id() . '--page--full');
	}
}

/**
 * Process related block right sidebar content
 */
function hwjma_preprocess_block__views_block__related_content_block_related_content(&$variables) {
  $journals_sidebar['items']['block_heading'] = $variables['label'];
  $journals_sidebar['items']['show_related_block'] = TRUE;
  $data_arr = $variables['elements']['content']['#view']->result;
  $journals_sidebar['items']['related_article'] = array();
  if (!empty($data_arr)) {
    foreach ($data_arr as $dataKey => $data) {
      $node  = $data->_object->getEntity();
      $node_id = $node->get('nid')->getValue()[0]['value'];
      $node_link = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'.$node_id);
      $title = $node->get('title_plain')->getValue()[0]['value'];
      $node_type = $node->bundle();
      switch ($node_type) {
        case HW_NODE_TYPE_ARTICLE:
          $item_type = 'Article';
        break;
        case HW_NODE_TYPE_CHAPTER:
          $item_type = 'Book chapter';
        break;
        case HW_NODE_TYPE_MONOGRAPH:
          $item_type = 'Monograph';
        break;
        case HW_NODE_TYPE_REPORT_GUIDELINE:
          $item_type = 'Report & Guideline';
        break;
        case HW_NODE_TYPE_TEST_REVIEW:
          $item_type = 'Test Review';
        break;
        default:
          $item_type = $item_type;
        break;
      }
      $journals_sidebar['items']['related_article'][$dataKey]['node_type'] = $item_type;
      $journals_sidebar['items']['related_article'][$dataKey]['node_title'] = $title;
      $journals_sidebar['items']['related_article'][$dataKey]['node_link'] = $node_link;
    }
  } else {
    $journals_sidebar['items']['show_related_block'] = FALSE;
  }
    $variables['journals_sidebar'] = $journals_sidebar;
}
