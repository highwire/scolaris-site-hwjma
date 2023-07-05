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
 * Implements hook_preprocess_HOOK().
 */
function hwjma_preprocess_page(&$variables) {

  // Add custom header auth regions.
  foreach(['header_auth', 'header_inst_branding', 'header_inst_branding_2'] as $region) {
    $variables['page'][$region] = [
      '#theme_wrappers' => ['region'],
      '#region' => $region,
    ];
  }


 }

/**
 * Implements hook_preprocess_region();
 */
function hwjma_preprocess_region(&$variables) {
  if (empty($variables['region'])) {
    return;
  }
  switch ($variables['region']) {
    case 'header_nav':
    case 'header_auth':
      // Add variable and class to region if institutional access has been granted.
      $inst_access = FALSE;
      
      $variables['inst_access'] = $inst_access;
      if ($variables['region'] == 'header_auth') {
        // Build account menu.
        $menu = hwjma_get_menu_render_array('account');
        $sigma_login_link = [];
        $custom_message = '';

        // Add message text based on whether user is logged in as
        // in individual and/or institutional user.
        if (!$variables['logged_in']) {
          $variables['attributes']['class'][] = 'menu--account';
          if (!empty($menu['#items']['openid_connect_sams_sigma.wayflesslogin'])) {
            $sigma_login_link = &$menu['#items']['openid_connect_sams_sigma.wayflesslogin'];
          }
        } else {
          if (!empty($menu['#items']['openid_connect_sams_sigma.manage_access'])) {
            $sigma_login_link = &$menu['#items']['openid_connect_sams_sigma.manage_access'];
          }
          if ($inst_access) {
            $custom_message = t("You currently have access to both British Psychological Society content and personal account features such as bookmarking and alerts.");
          } else {
            $custom_message = t("You are currently signed into an British Psychological Society personal account but do NOT have content access. Personal accounts only provide access to certain features such as bookmarking and alerts. To see if your institution has a subscription to AccessEngineering, contact your librarian or check your library's database list for access instructions.");
          }
        }
        // Add message text to sigma login link.
        if (!empty($sigma_login_link) && !empty($custom_message)) {
          $link_query = $sigma_login_link['url']->getOption('query');
          //$link_query['custom_message'] = $custom_message;
          $sigma_login_link['url']->setOption('query', $link_query);
        }
        // Add login link / account menu.
        if (!$variables['logged_in'] && !empty($sigma_login_link)) {
          $variables['content'] = array();
        //  $variables['content']['login'] = Link::fromTextAndUrl('', $sigma_login_link['url'])->toRenderable();
          $variables['content']['login']['#title'] = [
            ['#markup' => t('Log in')],
          ];
          $variables['content']['login']['#attributes']['class'][] = 'dropdown-toggle user__icon';
        } else if ($variables['logged_in'] && !empty($menu['#items'])) {
          $variables['content'] = array();
          $variables['content']['menu'] = $menu;
        }
 
      }
    break;
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
 * Implements hook_preprocess_THEME();
 */
function hwjma_preprocess_highwire_tabs(&$variables) {
  $variables['attributes']['class'][] = 'tab-content';
  if (!empty($variables['highwire_tabs_links'])) {
    $variables['highwire_tabs_links']['#attributes']['class'][] = 'nav';
    $variables['highwire_tabs_links']['#attributes']['class'][] = 'nav-tabs';
    foreach($variables['highwire_tabs_links']['#links'] as $key => $item_tab) {
      $variables['highwire_tabs_links']['#links'][$key]['attributes']['class'][] = 'nav-link';
      if (array_search('is-active',$variables['highwire_tabs_links']['#links'][$key]['attributes']['class'] )) {
        $variables['highwire_tabs_links']['#links'][$key]['attributes']['class'][] = 'active';
      }
    }
  }
}

function hwjma_preprocess_highwire_tab(&$variables) {
  if (empty($variables['element'])) {
    return;
  }
  if (in_array($variables['element']['#group'], ['group_journal_tabs'])) {
    if ($variables['element']['#group_name'] == 'group_full_text') {
      // Full text tab has 2cols, along w/ the sidebar makes the layout 3col.
      $variables['attributes']['class'][] = 'row--3col';
    } else {
      // By default chapter tabs & book tabs should be a main content column.
      if (array_search('is-active',$variables['attributes']['class'])) {
        $variables['attributes']['class'][] = 'active';
      }
      $variables['attributes']['class'][] = 'tab-pane';
    }
  }
}

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function hwjma_theme_suggestions_highwire_tabs_alter(array &$suggestions, array $variables) {
  switch ($variables['element']['#group_name']) {
    // Tabs with a right-hand sidebar.
    case 'group_journal_tabs':
    case 'group_hw_item_tabs':
      $suggestions[] = 'highwire_tabs__bps_tabs_rhs';
    break;
  }
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
  dump($variables);
}











