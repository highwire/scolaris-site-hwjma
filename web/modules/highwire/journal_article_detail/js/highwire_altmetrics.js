/**
 * @file
 *
 * Behaviors for the AltMetrics widget.
 */

(function ($) {
    Drupal.behaviors.HighWire_AltMetrics = {
      attach: function (context, settings) {
  
        // Check MutationObserver has support for IE.
        // If no support for IE, then load polyfill MutationObserver.
        if (typeof Modernizr != "undefined" && !Modernizr.mutationobserver) {
          if(typeof(Drupal.settings.MutationObserver) != "undefined" && Drupal.settings.MutationObserver !== null) {
            var mutationObserverJs = Drupal.settings.MutationObserver.js;
            // Using $.when to load polyfill before creating MutationObserver object.
            $.when( $.getScript( mutationObserverJs ) ).done(function () {
              mutationObserverDetect();
            });
          }
        }
        else {
          mutationObserverDetect();
        }
  
        // Listen to DOM changes on the widget wrapper. The service is a single JS resource, which either fails or succeeds.
        // The is no known way of detecting the success so we need this little trick to hide/show pane.
        function mutationObserverDetect() {
          var target = $('.altmetric-embed');
          var altmetricspane =$('.pane-highwire-altmetrics');
          var observer = new MutationObserver(function(mutations, observer) {
            var self = this;
            // Fired when a mutation occurs and the widget is present somewhere in the DOM.
            if (target[0]) {
              mutations.forEach(function(mutation) {
                if (mutation.type == 'attributes') {
                  if (target.hasClass('altmetric-hidden')) {
                    // If no altmetrics widget returned, hide the pane, next panel-separator sibling and kill the connection.
                    altmetricspane.hide().next('.panel-separator').hide();
                    self.disconnect();
                  }
                }
              });
            }
          });
          var config = {attributes: true, subtree: true};
          observer.observe(target[0], config);
        }
      }
    }
  })(jQuery);