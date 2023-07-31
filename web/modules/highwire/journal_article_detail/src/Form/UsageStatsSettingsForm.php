<?php

/**
 * @file
 * Contains Drupal\journal_article_detail\Form\UsageStatsSettingsForm.
 */

namespace Drupal\journal_article_detail\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class UsageStatsSettingsForm.
 *
 * @package Drupal\journal_article_detail\Form
 */
class UsageStatsSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'journal_article_detail.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'usage_stats_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('journal_article_detail.settings');

    $form['default_view'] = [
        '#type' => 'radios',
        '#title' => $this->t('DEFAULT VIEW'),
        '#options' => [
          0 => $this->t('Article lifetime'),
          1 => $this->t('Last 6 Months'),
          2 => $this->t('This Month'),
        ],
        '#default_value' => !empty($config->get('default_view')) ? $config->get('default_view') : 0,
    ];

    $form['date_filters'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('DATE FILTERS'),
      '#options' => array('ArticleLifetime' => $this->t('Article lifetime'), 'LastSixMonths' => $this->t('Last 6 Months'),  'ThisMonth' => $this->t('This Month')),
      '#default_value' => !empty($config->get('date_filters')) ? $config->get('date_filters') : array('ArticleLifetime', 'LastSixMonths', 'ThisMonth'),
    );

    $form['display_settings'] = [
        '#type' => 'radios',
        '#title' => $this->t('DISPLAY SETTINGS'),
        '#options' => [
          0 => $this->t('Chart Only'),
          1 => $this->t('Table Only'),
          2 => $this->t('Both Chart and Table'),
        ],
        '#default_value' => !empty($config->get('display_settings')) ? $config->get('display_settings') : 1,
        '#attributes' => ['data-default-value' => 1],
    ];

    $form['metric_types'] = array(
        '#type' => 'checkboxes',
        '#title' => $this->t('SELECT WHICH TYPE OF METRICS TO SHOW ON THE GRAPH AND TABLE.'),
        '#options' => array('Abstract' => $this->t('Abstract'), 'Full' => $this->t('Full'),  'PDF' => $this->t('PDF')),
        '#default_value' => !empty($config->get('metric_types')) ? $config->get('metric_types') : array('Abstract', 'Full', 'PDF'),
    );

    return parent::buildForm($form, $form_state);
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

    $this->config('journal_article_detail.settings')
      ->set('default_view', $form_state->getValue('default_view'))
      ->set('date_filters', $form_state->getValue('date_filters'))
      ->set('display_settings', $form_state->getValue('display_settings'))
      ->set('metric_types', $form_state->getValue('metric_types'))
      ->save();
  }

}