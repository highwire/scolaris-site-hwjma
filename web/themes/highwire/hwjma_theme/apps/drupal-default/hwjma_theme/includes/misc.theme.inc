<?php

/**
 * @file
 * Functions to support theming miscellaneous things the Particle theme.
 */

use Drupal\hwjma\Particle;
use Drupal\Core\Link;

/**
 * Implements hook_preprocess().
 */
function hwjma_preprocess(&$variables) {
  // Set the paths.assets variable globally, so that drupal can
  // find webpack-compiled assets.
  $variables['paths']['assets'] = Particle::ASSETS_PATH;
  $variables['paths']['images'] = Particle::IMAGES_PATH;
  $variables['paths']['fonts'] = Particle::FONTS_PATH;
  // $variables['paths']['svgs'] = Particle::SVGS_PATH;
}

/**
 * Implements hook_page_attachments_alter().
 */
function hwjma_page_attachments_alter(array &$page) {
  // Tell IE to use latest rendering engine (not to use compatibility mode).
  $ie_edge = [
    '#type' => 'html_tag',
    '#tag' => 'meta',
    '#attributes' => [
      'http-equiv' => 'X-UA-Compatible',
      'content' => 'IE=edge',
    ],
  ];
  $page['#attached']['html_head'][] = [$ie_edge, 'ie_edge'];
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
      if ($variables['region'] == 'header_auth') {

        // Build account menu.
        $menu = hwjma_get_menu_render_array('account');
        $sigma_login_link = [];
        if (!$variables['logged_in']) {
          if (!empty($menu['#items']['hwjma_core.idp.login'])) {
            $sigma_login_link = &$menu['#items']['hwjma_core.idp.login'];
          }
        }
        // Add login link / account menu.
        if (!$variables['logged_in'] && !empty($sigma_login_link)) {
          $variables['content'] = [
            'login' => Link::fromTextAndUrl('', $sigma_login_link['url'])->toRenderable(),
          ];
          $variables['content']['login']['#title'] = [
            '#markup' => t('My Account'),
          ];
          $variables['content']['login']['#attributes']['class'][] = 'user__icon';
        }
        elseif ($variables['logged_in'] && !empty($menu['#items'])) {
          if (!empty($menu['#items']['openid_connect_sams_sigma.manage_access'])) {
            unset($menu['#items']['openid_connect_sams_sigma.manage_access']);
          }
          if (!empty($menu['#items']['user.page'])) {
            unset($menu['#items']['user.page']);
          }
          $variables['content'] = [
            'menu' => $menu,
          ];
        }
      }
      break;
  }
}
