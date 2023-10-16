<?php

/**
 * @file
 * Contains Drupal\journal_article_detail\Form\EmailArticleConfigForm.
 */

namespace Drupal\hwjma_monsido\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class EmailArticleConfigForm.
 *
 * @package Drupal\journal_article_detail\Form
 * Use for Email Article backend configuration form : JCOREX-343
 */
class MonsidoForm extends FormBase {

  public function getFormId() {
    return 'MonsidoConfigForm';
  }
      
      public function buildForm(array $form, FormStateInterface $form_state) {
        $form['monsido_token'] = array(
          '#type' => 'textfield',
          '#title' => t('Token value'),
          '#required' => TRUE,
        );
        $form['monsido_privacy_reg'] = array(
            '#type' => 'select',
            '#title' => t('Monsido privacy regulation'),
            '#options' => [
                '1' => $this->t('CCPA'),
                '2' => $this->t('GDPR'),
                '3' => $this->t('CCPA+GDPR'),
              ],
        );
        $form['monsido_setting_fieldset'] = [
            '#type' => 'fieldset',
            '#title' => t('Monsido settings'),
        ];
        $form['monsido_setting_fieldset']['monsido_manual_startup'] = [
            '#type' => 'checkbox',
            '#title' => t('Manual startup'),
        ];
        $form['monsido_setting_fieldset']['monsido_hide_accept'] = [
            '#type' => 'checkbox',
            '#title' => t('Hide on accepted '),
        ];
        $form['monsido_setting_fieldset']['cat_const'] = [
            '#type' => 'checkbox',
            '#title' => t('Category consent'),
        ];
        $form['monsido_setting_fieldset']['monsido_rjct_option'] = [
            '#type' => 'checkbox',
            '#title' => t('Explicit reject option'),
        ];
        $form['monsido_setting_fieldset']['monsido_overlay'] = [
            '#type' => 'checkbox',
            '#title' => t('Has overlay'),
        ];
        $form['monsido_state_fieldset'] = [
            '#type' => 'fieldset',
            '#title' => t('Monsido theme settings'),
        ];
        $form['monsido_state_fieldset']['monsido_button_color'] = [
            '#type' => 'textfield',
            '#title' => t('Button color'),
            '#required' => TRUE,
        ];
        $form['monsido_state_fieldset']['monsido_button_text_color'] = [
            '#type' => 'textfield',
            '#title' => t('Button text color'),
            '#required' => TRUE,
        ];
        $form['monsido_state_fieldset']['monsido_icon_pic_url'] = [
            '#type' => 'textfield',
            '#title' => t('Icon picture url'),
            '#required' => TRUE,
        ];
        $form['monsido_state_fieldset']['monsido_icon_shape'] = [
            '#type' => 'textfield',
            '#title' => t('Icon shape'),
            '#required' => TRUE,
        ];
        $form['monsido_state_fieldset']['monsido_icon_position'] = [
            '#type' => 'textfield',
            '#title' => t('Icon position'),
            '#required' => TRUE,
        ];
        
        $form['monsido_links_settings'] = [
            '#type' => 'fieldset',
            '#title' => t('Mansido links settings'),
        ];
        $form['monsido_links_settings']['monsido_policy_url'] = [
            '#type' => 'textfield',
            '#title' => t('Cookie policy URL'),
        ];
        $form['monsido_links_settings']['monsido_opt_out'] = [
            '#type' => 'textfield',
            '#title' => t('Opt-out URL'),
        ];

        $form['actions']['#type'] = 'actions';
        $form['actions']['submit'] = array(
          '#type' => 'submit',
          '#value' => $this->t('Save configuration'),
          '#button_type' => 'primary',
        );
        return $form;
      }
      
      public function validateForm(array &$form, FormStateInterface $form_state) {

      }
      

      public function submitForm(array &$form, FormStateInterface $form_state) {

      }

}