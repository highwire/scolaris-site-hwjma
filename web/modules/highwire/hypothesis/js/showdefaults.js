/* 
 * Function for returning hypothesisConfig object based on
 * configuration of drupalSettings.
 */
(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.hypothesis = {
    attach: function (context, settings) {
      $('body', context).once('hypothesis-theme').each(function(i){
        var $this = $(this);
        // If hypothesis is loaded on the page, add a class to
        // the body wrapper so layouts can account for the sidebar.
        if ($this.hasClass('hypothesis') && typeof hypothesisConfig !== 'undefined') {
          var config = hypothesisConfig();
          // The theme option can be 'classic' or 'clean'. 'Classic' is the default.
          var hypothesisTheme = config.hasOwnProperty('theme') ? config.theme : 'classic';
          $this.addClass('hypothesis--' + hypothesisTheme);
        }
      });
    },
  };


  // See: https://h.readthedocs.io/projects/client/en/latest/publishers/config/#client-behavior
  window.hypothesisConfig = function () {
    return {
      showHighlights: drupalSettings.hypothesis.defaults.highlights,
      openSidebar: drupalSettings.hypothesis.defaults.sidebar,
    }
  }

})(jQuery, Drupal, drupalSettings)