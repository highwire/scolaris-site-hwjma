<?php

/**
 * @file
 * Functions to support theming media entities in the Particle theme.
 */

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 *
 * @TODO evaluate for Search.
 */
function hwjma_theme_suggestions_form_alter(array &$suggestions, array $variables) {
  $id = $variables['element']['#id'];
  // Particle Theme for Octane's Views Search and Drupal's Built-In Search.
  // This hook allows to apply two search block implementations to the same
  // theme file. Drupal only template file located at
  // /Particle/apps/drupal-default/templates/form/form--particle-search.html.twig.
  if ($id == 'views-exposed-form-search-page' || $id == 'search-block-form') {
    $suggestions[] = 'form__particle-search';
  }
  if ($id == 'views-exposed-form-search-page-1') {
    $suggestions[] = 'form__search_exposed_form';
  }
  $suggestions[] = 'form__' . str_replace('-','_', $id);
}


/**
 * Implements hook_theme_suggestions_block_alter().
 */
function hwjma_theme_suggestions_block_alter(array &$suggestions, array $variables) {
  // Provide suggestion for block templates by custom block type.
  if (!empty($variables['elements']['content']['#block_content'])) {
    $block = $variables['elements']['content']['#block_content'];
    // Add `block--BLOCK-TYPE.html.twig`.
    $suggestions[] = 'block__' . $block->bundle();
    $view_mode = $variables['elements']['#configuration']['view_mode'];
    if (!empty($view_mode)) {
      // Add `block--BLOCK-TYPE--VIEW-MODE.html.twig`.
      $suggestions[] = 'block__' . $block->bundle() . '__' . $view_mode;
    }
  }
  // Add template suggestions to menu blocks based on left region position.
  if ($variables['elements']['#base_plugin_id'] == 'system_menu_block') {
    $menu_name = $variables['elements']['#derivative_plugin_id'];
    // Load block to get region.
    $id = !empty($variables['elements']['#id']) ? $variables['elements']['#id'] : '';
    $block = !empty($id) ? \Drupal\block\Entity\Block::load($id) : '';
    $region = !empty($block) ? $block->getRegion() : '';
    // Check in configuration for region (e.g. for menu blocks in panels).
    if (empty($region) && !empty($variables['elements']['#configuration']['region'])) {
      $region = $variables['elements']['#configuration']['region'];
    }
    switch ($region) {
      case 'left':
        // Add left sidebar menu style.
        $suggestions[] = 'block__' . $variables['elements']['#base_plugin_id'] . '__sidebar';
        $suggestions[] = 'block__' . $variables['elements']['#base_plugin_id'] . '__' . $menu_name . '__sidebar';
      break;
    }
  }
}


/**
 * Implements hook_theme_suggestions_field_alter().
 */
function hwjma_theme_suggestions_field_alter(array &$suggestions, array $variables) {
  $element = $variables['element'];
  $suggestions[] = 'field__' . $element['#view_mode'];
  $suggestions[] = 'field__' . $element['#view_mode'] . '__' . $element['#field_name'];
}

