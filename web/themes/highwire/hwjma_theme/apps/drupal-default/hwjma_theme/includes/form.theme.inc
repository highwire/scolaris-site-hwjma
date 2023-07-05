<?php

/**
 * @file
 * Functions to support theming forms in the Particle theme.
 */

function hwjma_form_alter(&$form, \Drupal\Core\Form\FormStateInterface $form_state, $form_id) {
  if ($form_id == 'views_exposed_form') {
    $form['actions']['submit']['#attributes']['class'][] = 'btn btn-search';
  }
}