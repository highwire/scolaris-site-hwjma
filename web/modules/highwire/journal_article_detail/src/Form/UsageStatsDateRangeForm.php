<?php
/**
 * @file
 * Contains Drupal\journal_article_detail\Form\UsageStatsDateRangeForm.
 */

namespace Drupal\journal_article_detail\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UsageStatsDateRangeForm.
 *
 * @package Drupal\journal_article_detail\Form
 * Use for Usage Stats date range filter form which is configure base : JCOREX-102
 */
class UsageStatsDateRangeForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'usage_stats_date_range_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $nid = NULL) {
    $form['filter_by_date'] = array(
      '#type' => 'fieldset',
      '#title' => 'Select a custom date range',
      '#collapsible' => TRUE,
    );

    $form['filter_by_date']['start_date'] = array(
      '#type' => 'date',
      '#date_format' => 'Y-m-d',
      '#prefix' => '<div class="filter-by-date">',
    );

    $form['filter_by_date']['end_date']  = array(
      '#type' => 'date',
      '#date_format' => 'Y-m-d',
      '#prefix' => '<span class="date-spacer"> to </span>',
      '#suffix' => '</div>',
    );

    $form['filter_by_date']['nid'] = array(
      '#type' => 'hidden',
      '#value' => $nid,
    );

    $form['actions'] = [
      '#type' => 'button',
      '#value' => $this->t('Submit'),
      '#ajax' => [
        'callback' => '::setMessage',
      ],
      '#attributes' => ['class' => ['btn btn-primary usagestatstab daterangebutton']],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
  }

  /**
   * Ajax callback function
   */
  public function setMessage(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $build = [];
    $block_manager = \Drupal::service('plugin.manager.block');
    // Get backend configurations for usage stats
    $usageStatsConfig = \Drupal::config('journal_article_detail.settings');
    $usageMetricTypesFilter = $usageStatsConfig->get('metric_types');
    // Get avtive usage metric types
    $usageMetricTypesArray = [];
    foreach ($usageMetricTypesFilter as $key => $value) {
      if (!empty($value)) {
        $usageMetricTypesArray[] = $value;  
      }
    }
    $plugin_block = $block_manager->createInstance('hwjma_usage_stats', [
      'query_type' => $type,
      'views' => $usageMetricTypesArray,
      'date_format' => 'custom',
      'custom_date_format' => 'M Y',
      'limit' => '',
      'fromDate' => $form_state->getValue('start_date'),
      'toDate' => $form_state->getValue('end_date'),
    ]);
    $nid = $form_state->getValue('nid');
    $plugin_block->setContextValue('node', $nid);
    $render = $plugin_block->build();
    if (!empty($render)) {
      $build = $render;
    } 
    $Selector = '.ajax-wrapper';
    $content = '<p>Changed !!!</p>';
    $settings = ['my-setting' => 'setting',];
    $class = '.usagestatstab';
    $addmethod = 'addClass';
    $removemethod = 'removeClass';
    $arguments = ['active'];
    $id = '.daterangebutton';
    $response->addCommand(new InvokeCommand($class, $removemethod, $arguments));
    $response->addCommand(new InvokeCommand($id, $addmethod, $arguments));
    $response->addCommand(new ReplaceCommand($Selector, $build, $settings));
    return $response;
   }
}
