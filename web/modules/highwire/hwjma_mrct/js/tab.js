(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.customModuleBehavior = {
    attach: function (context, settings) {
      var $toggleButton = $(context).find('.article-section__abstract-btn');
      $toggleButton.once('hwjma_mrct').click(function () {
        var targetDivId = $(this).data("target");
        $("#" + targetDivId).toggle();
      });
    }
  };

})(jQuery, Drupal);
