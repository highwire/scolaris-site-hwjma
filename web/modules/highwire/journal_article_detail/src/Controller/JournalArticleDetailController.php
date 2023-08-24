<?php

namespace Drupal\journal_article_detail\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Symfony\Component\HttpFoundation\Request;

/**
 * An journal_article_detail controller.
 */
class JournalArticleDetailController extends ControllerBase {

  /**
   * Route callback for Ajax call.
   * Only works for Ajax calls.
   * Use for Usage Stats section tabs ajax call : JCOREX-102
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function content(Request $request) {
    // Check this is ajax call or not
    if (!$request->isXmlHttpRequest()) {
      throw new NotFoundHttpException();
    }
    
    $response = new AjaxResponse();
    $type = \Drupal::request()->query->get('type');
    $nid =  \Drupal::request()->query->get('nid');
    $usageStatsConfig = \Drupal::config('journal_article_detail.settings');
    $fromDate = '';
    $toDate = '';
    // Set date according to tab filter within Metric tab Article lifetime, Last 6 Months, This Month
    if ($type == 'LastSixMonths') {
        $fromDate = date("Y-m-d", mktime(0, 0, 0, date("m")-6, date("d"), date("Y")));
        $toDate = date("Y-m-d");
    } elseif ($type == 'ThisMonth') {
        $fromDate = date("Y-m-01");
        $toDate = date("Y-m-d");
    }
    $build = [];
    $usageMetricTypesFilter = $usageStatsConfig->get('metric_types');
    $usageMetricTypesArray = [];
    // Prepare usage stats data table views header: Abstract, FULL, PDF
    foreach ($usageMetricTypesFilter as $key => $value) {
      if (!empty($value)) {
        $usageMetricTypesArray[] = $value;  
      }
    }
    $block_manager = \Drupal::service('plugin.manager.block');
    $plugin_block = $block_manager->createInstance('hwjma_usage_stats', [
      'query_type' => $type,
      'views' => $usageMetricTypesArray,
      'date_format' => 'custom',
      'custom_date_format' => 'M Y',
      'limit' => '',
      'fromDate' => $fromDate,
      'toDate' => $toDate,
    ]);
    $plugin_block->setContextValue('node', $nid);
    $render = $plugin_block->build();
    if (!empty($render)) {
      $build = $render;
    } 
    $Selector = '.ajax-wrapper';
    $content = '<p>Changed !!!</p>';
    $settings = ['my-setting' => 'setting',];
    $id = '#'.$type;
    $class = '.usagestatstab';
    $addmethod = 'addClass';
    $removemethod = 'removeClass';
    $arguments = ['active'];
    $response->addCommand(new InvokeCommand($class, $removemethod, $arguments));
    $response->addCommand(new InvokeCommand($id, $addmethod, $arguments)); 
    $response->addCommand(new ReplaceCommand($Selector, $build, $settings));     
    return $response;
  }
}
