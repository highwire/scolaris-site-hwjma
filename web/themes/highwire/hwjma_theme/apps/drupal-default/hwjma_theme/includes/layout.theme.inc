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
    'facet_block__modal' => [
      'base hook' => 'facet_block',
    ],
    'facet_block__collapse' => [
      'base hook' => 'facet_block',
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
/* function hwjma_preprocess_region(&$variables) {
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
      $datavis_settings = \Drupal::config('scolaris_hwjma_datavis.settings');
      $is_datavis = !empty($datavis_settings->get('datavis_page_route')) && \Drupal::routeMatch()->getRouteName() == $datavis_settings->get('datavis_page_route');
      if ($is_datavis && !empty($datavis_settings->get('datavis_content_identifiers'))) {
        $sigma_extra_data = \Drupal::service('request_stack')->getCurrentRequest()->getSession()->get('sigma_extra_data');
        $key = implode('_', $datavis_settings->get('datavis_content_identifiers'));
        $inst_access = !empty($sigma_extra_data['highwire_sams_sigma']['org_licensed_profiles'][$key]);
      } else {
        $inst_access = \Drupal::service('highwire_user.hw_user_manager')->isInstitutionalUser();
      }
      if ($inst_access) {
        $variables['attributes']['class'][] = 'is-inst-access';
      }
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
          if ($inst_access) {
            $custom_message = t("You already have full content access to British Psychological Society through your institution. Some site functionality such as bookmarking and alerts require a personal account in addition to content access. To Sign In or Register for a personal account, select Log in via email/username.");
          } else {
            $custom_message = t("Registering for a Username and Password only provides access to certain functionality like bookmarking and alerts. It does NOT provide full content access. To see if your institution has a subscription to British Psychological Society, contact your librarian or check your library's database list for access instructions.");
            $variables['content'] = array();
            $variables['content']['login_prompt'] = ['#theme' => $is_datavis ? 'hwjma_login_prompt__datavis' : 'hwjma_login_prompt', '#weight' => -1];
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
          $variables['content']['login'] = Link::fromTextAndUrl('', $sigma_login_link['url'])->toRenderable();
          $variables['content']['login']['#title'] = [
            ['#markup' => t('Log in')],
          ];
          $variables['content']['login']['#attributes']['class'][] = 'dropdown-toggle user__icon';
        } else if ($variables['logged_in'] && !empty($menu['#items'])) {
          $variables['content'] = array();
          $variables['content']['menu'] = $menu;
        }

       // Add blocks. and  Add shopping cart.
     /*   $block_manager = \Drupal::service('plugin.manager.block');
        $current_user = \Drupal::currentUser();
        $cart_block = $block_manager->createInstance('highwire_ecommerce_shopping_cart', []);
        $cart_access = $cart_block->access($current_user);
        if (!(is_object($cart_access) && $cart_access->isForbidden() || is_bool($cart_access) && !$cart_access)) {
          $shopping_cart = $cart_block->build();
          $shopping_cart['#cache']['contexts'] = $cart_block->getCacheContexts();
          if (!empty($shopping_cart)) {
            $variables['shopping_cart'] = $shopping_cart;
          }
        }  */
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
      $suggestions[] = 'highwire_tabs__hwjma_tabs_rhs';
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
        ['#type' => 'hwjma_icon', '#icon' => 'arrow-circle-left', '#attributes' => ['class' => ['fa-lg']]],
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
        ['#type' => 'hwjma_icon', '#icon' => 'arrow-circle-right', '#attributes' => ['class' => ['fa-lg']]],
        ['#markup' => '<span>Next</span>'],
      ],
      '#attributes' => ['class' => ['article__next_link']],
    ];
    if ($variables['next_url']->isRouted() && $variables['next_url']->getRouteName() == '<nolink>') {
      $variables['next']['#attributes']['class'][] = 'disabled';
    }
  }
}

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function hwjma_theme_suggestions_views_view_fields_alter(array &$suggestions, array $variables) {
  if (!empty($variables['view'])) {
    switch ($variables['view']->id()) {
      case 'all_issues':
        $suggestions[] = 'views_view_fields__issue_archive';
      break;
    }
  }
}

/**
 * Implements hook_preprocess_views_view_fields__ID().
 */
function hwjma_preprocess_views_view_fields__issue_archive(&$variables) {
  if (!empty($variables['row']->_object)) {
    $search_api_id = !empty($variables['row']->search_api_id) ? $variables['row']->search_api_id : '';
    $search_api_id_parts = explode(':', $search_api_id);
    if ($search_api_id_parts[0] == 'entity') {
      $entity_type_id = explode('/', $search_api_id_parts[1]);
      $variables['issue_url'] = Url::fromRoute("entity.$entity_type_id[0].canonical", [$entity_type_id[0] => $entity_type_id[1]])->toString();
    }
  }
}

/*
* Implements hook_preprocess_THEME().
*/
function hwjma_preprocess_highwire_personalization_user_info(&$variables) {
  $current_user = \Drupal::currentUser();
  $username = $current_user->getUsername();
  $useremail = $current_user->getEmail();
  $variables['name'] = ucfirst($username);
  $variables['email'] = $useremail;
}

/**
 * Implements hook_preprocess__THEME().
 */
function hwjma_preprocess_menu__sidebar(&$variables) {
  if (empty($variables['items'])) {
    return;
  }
  $current_path = \Drupal::service('path.current')->getPath();
  $result = \Drupal::service('path.alias_manager')->getAliasByPath($current_path);
  $variables['current_path'] = $result;
  $variables['affix_nav'] = TRUE;
  $variables['show_menu'] = FALSE;
  foreach ($variables['items'] as &$item) {
    if (!empty($item['in_active_trail'])) {
      // Only show sidebar menu when the current page is part of any link's active trail.
      $variables['show_menu'] = TRUE;
      // Check if link is active.
      if ($item['url']->toString() == $variables['current_path']) {
        $item['is_active'] = TRUE;
      }
      // Check 2 levels of children for active items.
      if (!empty($item['below'])) {
        foreach ($item['below'] as &$item2) {
          if (!empty($item2['in_active_trail'])) {
            if ($item2['url']->toString() == $variables['current_path']) {
              $item2['is_active'] = TRUE;
            }
            if (!empty($item2['below'])) {
              foreach ($item2['below'] as &$item3) {
                if (!empty($item3['in_active_trail']) && $item3['url']->toString() == $variables['current_path']) {
                  $item3['is_active'] = TRUE;
                }
              }
            }
          }
        }
      }
    }
  }
  if (!$variables['show_menu']) {
		unset($variables['items']);
  }
}

/**
 * Implements hook_preprocess__THEME().
 */
function hwjma_preprocess_menu__main__sidebar(&$variables) {
  if (empty($variables['items'])) {
    return;
  }
  // Add 'is_active' flag to active links.
  hwjma_preprocess_menu__sidebar($variables);
}

/**
 * Implements hook_preprocess_THEME().
 */
function hwjma_preprocess_highwire_personalization_manage_saved_search(&$variables) {
  // Add icon & class to myaccount label run.
  if (!empty($variables['search_label']) && !empty($variables['execute_label']) && !empty($variables['search_link'])) {
    $variables['search_link']['#title'] = [
      'title' => [
        '#markup' => $variables['search_label'],
      ],
      'icon' => [
        '#type' => 'hwjma_icon',
        '#icon' => 'search-o',
        '#extra_classes' => 'fa-search',
        '#prefix' => '<span class="saved-search-execute-label">',
      ],
      'execute_title' => [
        '#markup' => $variables['execute_label'],
        '#suffix' => '</span>',
      ],
    ];
  }
}

/**
 * Implements hook_preprocess__THEME().
 */
function hwjma_preprocess_highwire_personalization_manage_marker(&$variables) {
  $node = $variables['marker_link']['#node'];
  if ($node == NULL) {
    $nid = ($variables['marker_link']['#url'])->getrouteParameters()['node'];
    $node = Node::load($nid);
  }
  if ($node) {
    $variables = search_result($variables, $node);
    $variables['result']['url'] = \Drupal::service('path_alias.manager')->getAliasByPath('/node/' . $variables['result']['id']);
    $variables['marker_link_date'] = $variables['element']['#created'];
    $variables['marker_link'] = ($variables['element']['links']['#items']['delete']['#url'])->toString();
    $variables['marker_popupdata'] = 'Are you sure remove your favourite ' . $variables['result']['title'] . '?';
    $variables['modaltargetid'] = "favorite-action" . uniqid();
  }
} 
