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

/**
 * Implements hook_preprocess_views_view__ID__DISPLAY().
 */
function hwjma_preprocess_views_view__search__page(&$variables) {  
  $current_user = \Drupal::currentUser();
  $request = \Drupal::request();
  $view = $variables['view'];
  $total = isset($view->total_rows) ? $view->total_rows : count($view->result);
  $variables['resultsCount'] = $total;
  $variables['searchText'] = '';
  if (isset($_GET['query'])) {
    $variables['searchTerm'] = !empty($_GET['query']) ? $_GET['query'] : t('all content');
    $variables['blankSearch'] = !empty($_GET['query']) ? $_GET['query'] : t('');
    $variables['searchText'] = !empty($_GET['query']) ? 'Your search for <span>' . $variables['searchTerm'] . '</span> returned <span>' . $variables['resultsCount'] . '</span> results' : '';
  }

  $header_inline_items = [
    'highwire_personalization_save_search' => 'save_search',
  ];
  foreach ($header_inline_items as $key => $var) {
    if (!empty($variables['header'][$key])) {
      $variables[$var] = $variables['header'][$key];
      unset($variables['header'][$key]);
    }
  }

 // Add exposed filter form if there is more than one result.
  if ($total > 0) {
    $form_state = new \Drupal\Core\Form\FormState();
    $form_state->setFormState([
      'view' => $view,
      'display' => $view->display_handler->display,
      'exposed_form_plugin' => $view->display_handler->getPlugin('exposed_form'),
      'method' => 'get',
      'rerender' => TRUE,
      'no_redirect' => TRUE,
      'always_process' => TRUE, // This is important for handle the form status.
    ]);
    $build_info = $form_state->getBuildInfo();
    $build_info['args']['items_per_page'] = TRUE;
    $form_state->setBuildInfo($build_info);
    $form = \Drupal::formBuilder()->buildForm('Drupal\views\Form\ViewsExposedForm', $form_state);

    // Build 'Sort by' as HTML links due to bug: https://www.drupal.org/project/drupal/issues/2823541
    $params = $request->query->all();
    $current_sort_by = $request->query->get('sort_by') ?? 'search_api_relevance';
    $sort_by_options = array_keys($form['sort_by']['#options']);

    foreach ($sort_by_options as $sort) { 
      if ($current_sort_by == $sort) { 
        $sort_by_output[] = ['button' => [
            'title' => $form['sort_by']['#options'][$sort],
            'text' => $form['sort_by']['#options'][$sort],
          ]];
      }
      else {
        $params['sort_by'] = $sort;
        $params['page'] = 0;
        $sort_by_output[] = ['button' => [
            'title' => $form['sort_by']['#options'][$sort],
            'href' =>  $request->getPathInfo() . '?' . http_build_query($params),
            'text' => $form['sort_by']['#options'][$sort],
          ]];
      }
    }
    $variables['order_by'] = $sort_by_output; 

    unset($form['sort_by']);
    $form['sort_by']['#markup'] = rtrim($sort_by_output, '| ');
    $form['sort_by']['#allowed_tags'] = ['a'];

    // Build 'Items per page' as HTML links due to bug: https://www.drupal.org/project/drupal/issues/2823541
    if (!empty($form['items_per_page'])) {
      $query = $request->query->all();
      if (isset($query['page'])) {
        unset($query['page']);
      }
      $url = Url::fromUserInput($request->getPathInfo(), ['query' => $query]);
      $show_per_page_arr = array();
      $params = $request->query->all();
      if (!empty($form['items_per_page']['#options'])) {
        foreach ($form['items_per_page']['#options'] as $show_per_page_options) {
          $params['items_per_page'] = $show_per_page_options;
          $show_per_page_arr[] = ['button' => [
            'title' => $show_per_page_options,
            'href' =>  $request->getPathInfo() . '?' . http_build_query($params),
            'text' => $show_per_page_options,
          ]];
        }
      }
      $variables['page_count'] = $show_per_page_arr; 
    }
  }

  // Add facet blocks.
  $facet_blocks = [
    'content_type_facet' => 'Include content types...',
    'keywords' => 'Keywords',
    'author' => 'Author',
   'article_type' => 'Article Type',
    'journal_title_facet' => 'Journol Title',
  ];

  $facets_render = [];
  $block_manager = \Drupal::service('plugin.manager.block');
  foreach ($facet_blocks as $facet_plugin_id => $facet_label) {
    $plugin_block = $block_manager->createInstance('facet_block:' . $facet_plugin_id);
    $access_result = $plugin_block->access($current_user);
    if (!(is_object($access_result) && $access_result->isForbidden() || is_bool($access_result) && !$access_result)) {
      $render = $plugin_block->build();

      // Don't add placeholder containers for ajax facets if ajax is not enabled.
      // Ajax placeholders will be set up as a container with class 'facet-empty'.
      if (!$view->display_handler->ajaxEnabled() && !empty($render[0]['#type']) && $render[0]['#type'] == 'container' && !empty($render[0]['#attributes']['class']) && in_array('facet-empty', $render[0]['#attributes']['class'])) {
        continue;
      }

      if (!empty($render)) {
        $facets_render[$facet_plugin_id] = [
          '#theme' => 'facet_block',
          '#facet' => $render,
          '#facet_id' => $facet_plugin_id,
          '#title' => $facet_label,
        ];
        if (isset($render['#contextual_links'])) {
          $facets_render[$facet_plugin_id]['#contextual_links'] = $render['#contextual_links'];
        }
      }
    }
  }

  $plugin_block = $block_manager->createInstance('hwjma_search_date_filter');
    $access_result = $plugin_block->access(\Drupal::currentUser());
    if (!(is_object($access_result) && $access_result->isForbidden() || is_bool($access_result) && !$access_result)) {
      $render = $plugin_block->build();
      if ((!empty($render) && !empty($total)) || !empty($render['active_items']['#items'])) {
        $facets_render['hwjma_search_date_filter'] = [
          '#theme' => 'facet_block',
          '#facet' => $render,
          '#facet_id' => 'hwjma_search_date_filter',
          '#title' => 'Publication Date',
        ];
      }
    }

    if (!empty($facets_render)) {
      foreach ($facets_render as $key => $value) {
        if ($key == 'test_type' || $key == 'test_publisher' || $key == 'show_registered_tests_only') {
          $facets_render[$key] += ['show_facet' => FALSE];
        }
        else {
          $facets_render[$key] += ['show_facet' => TRUE];
        }
      }
    
      $url = $request->getUri();
      $url_components = parse_url($url);

      parse_str($url_components['query'], $params);

      if (!empty($params['f'])) {
        foreach ($params['f'] as $key => $value) {
          if ($value == 'content_type_facet:test-review-item-test-review') {
            $facets_render['test_type']['show_facet'] = TRUE;
            $facets_render['test_publisher']['show_facet'] = TRUE;
            $facets_render['show_registered_tests_only']['show_facet'] = TRUE;
            break;
          }
        }
      }
    }
    if (!empty($facets_render)) {
      if (isset($facets_render['content_type_facet'])) {
        $facets_render['content_type_facet']['#suffix'] = 'Filter results by...';
        $facets_render['content_type_facet']['#facet__heading'] = 'Include content types...';
      }
      $variables['facets'] = $facets_render;
    }
}

/**
 * Implements hook_preprocess_facets_item_list__HOOK().
 */
function hwjma_preprocess_facets_item_list__hwjmacheckbox_content_type(&$variables) {
  if ($variables['facet']->getName() == "Show registered tests only") {
    $variables['items'][0]['value']['#title']['#value'] = "Show registered tests only";
    $variables['items'][0]['value']['#title']['#raw_value'] = "Show registered tests only";
  }
}