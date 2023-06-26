<?php

use \Drupal\Component\Utility\Html;
use \Drupal\Core\Url;
use \Drupal\Core\Link;
use \Drupal\Core\Template\Attribute;
use \Drupal\Core\Render\Element;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\block\Entity\Block;
use Drupal\node\Entity\Node;

/**
 * @file
 * Functions to support theming forms in the Particle theme.
 */

/**
 * Implements hook_theme()
 */
function hwjma_theme(array $existing, $type, $theme, $path) {
  return [
    'list_items_per_page' => [
      'variables' => ['options' => [], 'url_parameter' => 'items_per_page', 'url' => FALSE, 'total' => 0],
    ],
    'hwjma_page_layout' => [
      'template' => 'layout/hwjma-page-layout',
      'render element' => 'content',
    ],
    'facet_block' => [
      'template' => 'facet-block',
      'variables' => ['title' => '', 'facet' => '', 'contextual_links' => '', 'facet_id' => ''],
    ],
  ];
}

/**
 * Implements hook_preprocess_THEME().
 */
function hwjma_preprocess_hwjma_page_layout(&$variables) {
  if (empty($variables['content']['#layout'])) {
    return;
  }
  $variables['settings'] = isset($variables['content']['#settings']) ? $variables['content']['#settings'] : [];
  $variables['layout'] = $variables['content']['#layout'];
  // Build rows.
  $layout_regions = $variables['layout']->getRegions();
  $default_region = $variables['layout']->getDefaultRegion();
  $rows = [];
  foreach ($layout_regions as $region_id => $region_settings) {
    // Skip empty regions or regions that are not assigned to a row.
    if (empty($variables['content'][$region_id]) || empty($region_settings['row'])) {
      continue;
    }
    $row = $region_settings['row'];
    if (!isset($rows[$row])) {
      $rows[$row] = [];
    }
    $attributes = new Attribute(['class' => ['col--' . Html::getClass($region_id)]]);
    if (!empty($region_settings['class'])) {
      $attributes->addClass(explode(' ', $region_settings['class']));
    }
    $rows[$row][$region_id] = [
      'id' => $region_id,
      'content' => $variables['content'][$region_id],
      'attributes' => $attributes,
    ];
  }
  if (!empty($rows)) {
    $variables['rows'] = $rows;
  }
  // Additional row classes.
  $additional = $variables['layout']->get('additional');
  if (!empty($additional['row_classes'])) {
    $variables['row_classes'] = $additional['row_classes'];
  }
}


/**
 * Implements hook_menu_render_array();
 */
function hwjma_get_menu_render_array($menu_name) {
  $menu_tree = \Drupal::menuTree();
  // Build the typical default set of menu tree parameters.
  $parameters = $menu_tree->getCurrentRouteMenuTreeParameters($menu_name);
  // Load the tree based on this set of parameters.
  $tree = $menu_tree->load($menu_name, $parameters);
  // Transform the tree using the manipulators you want.
  $manipulators = array(
    // Only show links that are accessible for the current user.
    array('callable' => 'menu.default_tree_manipulators:checkAccess'),
    // Use the default sorting of menu links.
    array('callable' => 'menu.default_tree_manipulators:generateIndexAndSort'),
  );
  $tree = $menu_tree->transform($tree, $manipulators);
  // Finally, build a renderable array from the transformed tree.
  $menu = $menu_tree->build($tree);
  return $menu;
}

/**
 * Implements hook_preprocess_hwjma_toc_pager().
 */
function hwjma_preprocess_hwjma_toc_pager(&$variables) {
  if (!empty($variables['previous_url'])) {
    $variables['previous'] = [
      '#type' => 'link',
      '#url' => $variables['previous_url'],
      '#title' => [
        ['#type' => 'bps_icon', '#icon' => 'arrow-circle-left', '#attributes' => ['class' => ['fa-lg']]],
        ['#markup' => '<span>Previous</span>'],
      ],
      '#attributes' => ['class' => ['article__previous_link']],
    ];
    if ($variables['previous_url']->isRouted() && $variables['previous_url']->getRouteName() == '<nolink>') {
      $variables['previous']['#attributes']['class'][] = 'disabled';
    }
  }
  if (!empty($variables['next_url'])) {
    $variables['next'] = [
      '#type' => 'link',
      '#url' => $variables['next_url'],
      '#title' => [
        ['#type' => 'bps_icon', '#icon' => 'arrow-circle-right', '#attributes' => ['class' => ['fa-lg']]],
        ['#markup' => '<span>Next</span>'],
      ],
      '#attributes' => ['class' => ['article__next_link']],
    ];
    if ($variables['next_url']->isRouted() && $variables['next_url']->getRouteName() == '<nolink>') {
      $variables['next']['#attributes']['class'][] = 'disabled';
    }
  }
}





