/** global: FC */
var FC = FC || {};

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.hwjma_ecommerce = {
    attach: function (context, settings) {
      FC.onLoadCallbacks.push(function() {
        /**
         * Collapse access panel when item is added to cart.
         */
        FC.client.event("highwire-ecommerce-cart-submitted").override(function (params) {
          collapseAccessPanel($(params.element).closest('.access-panel'));
        });

        /**
         * Customize display of list items in message.
         */
        FC.client.event('highwire-ecommerce-access-items-removed-message-build').override(function (params) {
          if (!params.items) {
            return;
          }
          // Build list.
          var $list = buildListItems(params.items, 'fc-cart__removed-items-list');
          var count = $list.children().length;
          if (count > 0) {
            // Add message text and list items.
            var removedQuantity = count === 1 ? '1 item has' : count + ' items have';
            $('#fc .fc-cart__custom-messages .fc-alert', context)
            .append('<p>' + removedQuantity + ' been removed from your cart because you already have access:</p>')
            .append($list);
          }
        });
      });

      /**
       * Collapse access panel.
       *
       * @param $panel
       *   The access panel to collapse.
       */
      function collapseAccessPanel($panel) {
        if ($panel.length > 0 && $panel.hasClass('in')) {
          var $trigger = $('[data-target="#' + $panel.attr('id') + '"].btn--access-expander', context).first();
          $trigger.trigger('click');
        }
      }

      /**
       * Build a list of cart item links.
       *
       * @param items
       *   A jquery object of cart items.
       * @param addClass
       *   An additional class to add to the list.
       *
       * @return
       *   The list as a jquery object.
       */
      function buildListItems(items, addClass) {
        var $list = $('<ul class="fc-items-list"></ul>').addClass(addClass);
        var count = 0;
        for (var apath in items) {
          if (!items.hasOwnProperty(apath)) {
            continue;
          }
          var title = items[apath]['title'];
          var type = '';
          if (items[apath]['product_options']) {
            type = (items[apath]['product_options']['for'] == 'ebook') ? 'book' : items[apath]['product_options']['for'];
            if (title && items[apath]['product_options']['title_suffix']) {
              title += ', ' + items[apath]['product_options']['title_suffix'];
            }
          }
          title = '<span class="title">' + title + '</span>';
          if (type) {
            title += ' <span class="type">(' + type + ')</span>';
          }
          $list.append('<li><a href="' + items[apath]['url'] + '">' + title + '</a></li>');
          count++;
        }
        if (count > 0) {
          return $list;
        }
      }
    }
  };

})(jQuery, Drupal, drupalSettings);
