<?php
/**
 * @file
 * Contains \Drupal\hwjma_basic_config\Plugin\Block\SocialMediaBlock.
 */
namespace Drupal\hwjma_basic_config\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Component\Render\FormattableMarkup;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "social_media_block",
 *   admin_label = @Translation("Social Media block"),
 * )
 */
class SocialMediaBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {

    $config = \Drupal::config('hwjma_basic_config.settings');

    $facebook = $config->get('footer_facebook');
    $twitter = $config->get('footer_twitter');
    $youtube = $config->get('footer_youtube');
    $linkedin = $config->get('footer_linkedin');

    $output = '<div class="flex flex-wrap">
    <div class="flex-grow w-full social-share">';

    if(!empty($twitter)){
      $output .= '<a class="twitter" href="'.$twitter.'" title="Follow on Twitter">
      <svg height="21px" version="1.1" viewBox="0 0 22 21" width="22px" xmlns="http://www.w3.org/2000/svg">
        <title></title>
        <g fill="none" fill-rule="evenodd" id="Symbols" stroke="none" stroke-width="1">
            <g id="twitter">
              <rect height="20.38" id="Rectangle" width="22" x="0" y="0"></rect>
              <path d="M22,3.11658333 C21.1905833,3.47591667 20.3206667,3.71791667 19.4076667,3.827 C20.3399167,3.26875 21.0558333,2.38416667 21.39225,1.33 C20.5205,1.847 19.5543333,2.22283333 18.5258333,2.42541667 C17.7035833,1.54816667 16.5293333,1 15.2313333,1 C12.31725,1 10.1759167,3.71883333 10.8340833,6.54125 C7.084,6.35333333 3.75833333,4.55666667 1.53175,1.82591667 C0.34925,3.8545 0.9185,6.50825 2.92783333,7.85208333 C2.189,7.82825 1.49233333,7.62566667 0.884583333,7.28741667 C0.835083333,9.37833333 2.33383333,11.3345 4.5045,11.7699167 C3.86925,11.94225 3.1735,11.9825833 2.46583333,11.8469167 C3.03966667,13.6399167 4.70616667,14.9443333 6.6825,14.981 C4.785,16.46875 2.39433333,17.1333333 0,16.851 C1.99741667,18.1315833 4.37066667,18.8786667 6.919,18.8786667 C15.2991667,18.8786667 20.03375,11.8010833 19.74775,5.45316667 C20.6295833,4.81608333 21.395,4.02133333 22,3.11658333 Z" fill="#FFFFFF" fill-rule="nonzero" id="Combined-Shape-Copy"></path>
            </g>
        </g>
      </svg>
      </a>';
    }

    if(!empty($facebook)){
      $output .= '<a class="facebook" href="'.$facebook.'" title="Follow on Facebook">
        <svg height="21px" version="1.1" viewBox="0 0 22 21" width="22px" xmlns="http://www.w3.org/2000/svg">
          <title></title>
          <g fill="none" fill-rule="evenodd" id="Symbols" stroke="none" stroke-width="1">
            <g id="fb">
              <rect height="20.38" id="Rectangle-Copy-6" width="22" x="0" y="0"></rect>
              <path d="M9.128,20.375 L9.128,11.081 L6,11.081 L6,7.459 L9.128,7.459 L9.128,4.788 C9.128,1.688 11.021,0 13.787,0 C15.112,0 16.25,0.099 16.582,0.143 L16.582,3.383 L14.664,3.384 C13.16,3.384 12.869,4.099 12.869,5.147 L12.869,7.46 L16.456,7.46 L15.989,11.082 L12.869,11.082 L12.869,20.375 L9.128,20.375 Z" fill="#FFFFFF" fill-rule="nonzero" id="Path-Copy"></path>
            </g>
          </g>
        </svg>
      </a>';
    }

    if(!empty($youtube)){
      $output .= '<a class="youtube" href="'.$youtube.'" title="Follow on YouTube">
        <svg height="21px" version="1.1" viewBox="0 0 22 21" width="22px" xmlns="http://www.w3.org/2000/svg">
          <title></title>
          <g fill="none" fill-rule="evenodd" id="Symbols" stroke="none" stroke-width="1">
            <g id="yt">
              <rect height="20.38" id="Rectangle-Copy-8" width="22" x="0" y="0"></rect>
              <polygon fill="#FFFFFF" fill-rule="nonzero" id="Path-Copy" points="3 18 3 2 19 9.986 3 18"></polygon>
            </g>
          </g>
        </svg>
      </a>';
    }

    if(!empty($linkedin)){
      $output .= '<a class="linkedin" href="'.$linkedin.'" title="Follow on LinkedIn">
        <svg height="21px" version="1.1" viewBox="0 0 22 21" width="22px" xmlns="http://www.w3.org/2000/svg">
          <title></title>
          <g fill="none" fill-rule="evenodd" id="Symbols" stroke="none" stroke-width="1">
            <g id="li">
              <rect height="20.38" id="Rectangle-Copy-7" width="22" x="0" y="0"></rect>
              <path d="M5.83606557,6.66085246 L5.83606557,19.644459 L2.29508197,19.644459 L2.29508197,6.66085246 L5.83606557,6.66085246 Z M11.7377049,6.66085246 L11.7377049,8.74413115 C13.3854426,5.69180328 20,5.46636066 20,11.666623 L20,19.644459 L16.4590164,19.644459 L16.4590164,13.0299016 C16.4590164,9.05455738 11.7377049,9.35554098 11.7377049,13.0299016 L11.7377049,19.644459 L8.19672131,19.644459 L8.19672131,6.66085246 L11.7377049,6.66085246 Z M4.06557377,1 C5.20577049,1 6.13114754,1.93245902 6.13114754,3.08209836 C6.13114754,4.2317377 5.20695082,5.16419672 4.06557377,5.16419672 C2.92537705,5.16419672 2,4.2317377 2,3.08209836 C2,1.93245902 2.92537705,1 4.06557377,1 Z" fill="#FFFFFF" fill-rule="nonzero" id="Combined-Shape-Copy"></path>
            </g>
          </g>
        </svg>
      </a>';
    }
      
    $output .= '</div>
        </div>';


        return [
          '#theme' => 'social_media_block',
          '#social' => $output,
        ];
  }
}