<?php

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
