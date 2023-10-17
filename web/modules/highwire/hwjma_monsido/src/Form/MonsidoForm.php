<?php

/**
 * @file
 * Contains Drupal\journal_article_detail\Form\EmailArticleConfigForm.
 */

namespace Drupal\hwjma_monsido\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class EmailArticleConfigForm.
 *
 * @package Drupal\journal_article_detail\Form
 * Use for Email Article backend configuration form : JCOREX-343
 */
class MonsidoForm extends ConfigFormBase {

    /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'hwjma_monsido.settings',
    ];
  }


  public function getFormId() {
    return 'MonsidoConfigForm';
  }
      
      public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('hwjma_monsido.settings');
        $form['monsido_token'] = array(
          '#type' => 'textfield',
          '#title' => t('Token value'),
          '#required' => TRUE,
          '#default_value' => !empty($config->get('monsido_token')) ? $config->get('monsido_token') : 0,
        );
        $form['monsido_privacy_reg'] = array(
            '#type' => 'select',
            '#title' => t('Monsido privacy regulation'),
            '#options' => [
                'ccpa' => $this->t('CCPA'),
                'gdpr' => $this->t('GDPR'),
                'ccpa-gdpr' => $this->t('CCPA+GDPR'),
              ],
            '#default_value' => !empty($config->get('monsido_privacy_regulation')) ? $config->get('monsido_privacy_regulation') : 0,
        );
        $form['monsido_setting_fieldset'] = [
            '#type' => 'fieldset',
            '#title' => t('Monsido settings'),
        ];
        $form['monsido_setting_fieldset']['manual_startup'] = [
            '#type' => 'checkbox',
            '#title' => t('Manual startup'),
            '#default_value' => !empty($config->get('manual_startup')) ? $config->get('manual_startup') : 0,
        ];
        $form['monsido_setting_fieldset']['hide_accept'] = [
            '#type' => 'checkbox',
            '#title' => t('Hide on accepted '),
            '#default_value' => !empty($config->get('hide_accept')) ? $config->get('hide_accept') : 0,
        ];
        $form['monsido_setting_fieldset']['cat_const'] = [
            '#type' => 'checkbox',
            '#title' => t('Category consent'),
            '#default_value' => !empty($config->get('cat_const')) ? $config->get('cat_const') : 0,
        ];
        $form['monsido_setting_fieldset']['rjct_option'] = [
            '#type' => 'checkbox',
            '#title' => t('Explicit reject option'),
            '#default_value' => !empty($config->get('rjct_option')) ? $config->get('rjct_option') : 0,
        ];
        $form['monsido_setting_fieldset']['overlay'] = [
            '#type' => 'checkbox',
            '#title' => t('Has overlay'),
            '#default_value' => !empty($config->get('overlay')) ? $config->get('overlay') : 0,
        ];
        $form['monsido_state_fieldset'] = [
            '#type' => 'fieldset',
            '#title' => t('Monsido theme settings'),
        ];
        $form['monsido_state_fieldset']['button_color'] = [
            '#type' => 'textfield',
            '#title' => t('Button color'),
            '#required' => TRUE,
            '#default_value' => !empty($config->get('button_color')) ? $config->get('button_color') : 0,
        ];
        $form['monsido_state_fieldset']['button_text_color'] = [
            '#type' => 'textfield',
            '#title' => t('Button text color'),
            '#required' => TRUE,
            '#default_value' => !empty($config->get('button_text_color')) ? $config->get('button_text_color') : 0,
        ];
        $form['monsido_state_fieldset']['icon_pic_url'] = [
            '#type' => 'textfield',
            '#title' => t('Icon picture url'),
            '#required' => TRUE,
            '#default_value' => !empty($config->get('icon_pic_url')) ? $config->get('icon_pic_url') : 0,
        ];
        $form['monsido_state_fieldset']['icon_shape'] = [
            '#type' => 'textfield',
            '#title' => t('Icon shape'),
            '#required' => TRUE,
            '#default_value' => !empty($config->get('icon_shape')) ? $config->get('icon_shape') : 0,
        ];
        $form['monsido_state_fieldset']['icon_position'] = [
            '#type' => 'textfield',
            '#title' => t('Icon position'),
            '#required' => TRUE,
            '#default_value' => !empty($config->get('icon_position')) ? $config->get('icon_position') : 0,
        ];
        
        $form['monsido_links_settings'] = [
            '#type' => 'fieldset',
            '#title' => t('Mansido links settings'),
        ];
        $form['monsido_links_settings']['cookie_policy_url'] = [
            '#type' => 'textfield',
            '#title' => t('Cookie policy URL'),
            '#default_value' => !empty($config->get('cookie_policy_url')) ? $config->get('cookie_policy_url') : 0,
        ];
        $form['monsido_links_settings']['opt_out_url'] = [
            '#type' => 'textfield',
            '#title' => t('Opt-out URL'),
            '#default_value' => !empty($config->get('opt_out_url')) ? $config->get('opt_out_url') : 0,
        ];

        $form['actions']['#type'] = 'actions';
        $form['actions']['submit'] = array(
          '#type' => 'submit',
          '#value' => $this->t('Save configuration'),
          '#button_type' => 'primary',
        );
        return parent::buildForm($form, $form_state);
      }
      
      public function validateForm(array &$form, FormStateInterface $form_state) {
        parent::validateForm($form, $form_state);
      }
      

      public function submitForm(array &$form, FormStateInterface $form_state) {
        parent::submitForm($form, $form_state);
        $this->config('hwjma_monsido.settings')
        ->set('monsido_token', $form_state->getValue('monsido_token'))
        ->set('button_color', $form_state->getValue('button_color'))
        ->set('button_text_color', $form_state->getValue('button_text_color'))
        ->set('icon_pic_url', $form_state->getValue('icon_pic_url'))
        ->set('icon_shape', $form_state->getValue('icon_shape'))
        ->set('icon_position', $form_state->getValue('icon_position'))
        ->set('cookie_policy_url', $form_state->getValue('cookie_policy_url'))
        ->set('opt_out_url', $form_state->getValue('opt_out_url'))
        ->set('monsido_privacy_regulation', $form_state->getValue('monsido_privacy_reg'))
        ->set('manual_startup', $form_state->getValue('manual_startup'))
        ->set('hide_accept', $form_state->getValue('hide_accept'))
        ->set('cat_const', $form_state->getValue('cat_const'))
        ->set('rjct_option', $form_state->getValue('rjct_option'))
        ->set('overlay', $form_state->getValue('overlay'))
        ->save();
      }

}