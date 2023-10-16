(function ($, Drupal) {
    Drupal.behaviors.myModuleBehavior = {
      attach: function (context, settings) {
        window._monsido = window._monsido || {
            token: "HBktah_nQR6S6dpnyfGZow",
        };
        window._monsidoConsentManagerConfig = window._monsidoConsentManagerConfig || {
            token: "HBktah_nQR6S6dpnyfGZow",
            privacyRegulation: ["ccpa","gdpr"],
            settings: {
                manualStartup: false,
                hideOnAccepted: false,
                perCategoryConsent: true,
                explicitRejectOption: false,
                hasOverlay: false,
            },
            i18n: {
                languages: ["en_US"],
                defaultLanguage: "en_US"
            },
            theme: {
                buttonColor: "#783CE2",
                buttonTextColor: "#ffffff",
                iconPictureUrl: "cookie",
                iconShape: "circle",
                position: "bottom-right",
            },
            links: {
                cookiePolicyUrl: "",
                optOutUrl: "",
            },
        };
      }
    };
  })(jQuery, Drupal, drupalSettings );