<?php

namespace Drupal\journal_article_detail\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Symfony\Component\HttpFoundation\Request;

/**
 * An journal_article_detail controller.
 */
class JournalArticleDetailController extends ControllerBase {

  /**
   * Route callback for Ajax call.
   * Only works for Ajax calls.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function content(Request $request) {
    if (!$request->isXmlHttpRequest()) {
      throw new NotFoundHttpException();
    }

    $response = new AjaxResponse();

    $type = \Drupal::request()->query->get('type');
    $nid =  \Drupal::request()->query->get('nid');

    $build = [];
    $block_manager = \Drupal::service('plugin.manager.block');
    $plugin_block = $block_manager->createInstance('hwjma_usage_stats', [
      'query_type' => $type,
      'views' => ['abstract', 'full', 'pdf', 'total'],
      'date_format' => 'custom',
      'custom_date_format' => 'M Y',
      'limit' => '',
    ]);
    $plugin_block->setContextValue('node', $nid);
    $render = $plugin_block->build();
    if (!empty($render)) {
      $build = $render;
    } 

    $Selector = '.ajax-wrapper'; //See: https://www.w3schools.com/cssref/css_selectors.asp
    $content = '<p>Changed !!!</p>'; /*The content that will be replace the matched element(s), either a render array or an HTML string.*/
    $settings = ['my-setting' => 'setting',]; /*An array of JavaScript settings to be passed to any attached behaviors.*/
    $response->addCommand(new ReplaceCommand($Selector, $build, $settings));
    return $response;
  }

}