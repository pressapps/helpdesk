<?php
/**
 * Scripts and stylesheets
 *
 * Enqueue stylesheets in the following order:
 * 1. /theme/assets/css/main.css
 *
 * Enqueue scripts in the following order:
 * 1. jquery-1.11.1.min.js via Google CDN
 * 2. /theme/assets/js/vendor/modernizr.min.js
 * 3. /theme/assets/js/scripts.js
 *
 * Google Analytics is loaded after enqueued scripts if:
 * - An ID has been defined in config.php
 * - You're not logged in as an administrator
 */
function roots_scripts() {
  /**
   * The build task in Grunt renames production assets with a hash
   * Read the asset names from assets-manifest.json
   */
  if (WP_ENV === 'development') {
    $assets = array(
      'css'       => '/assets/css/main.css',
      'ionicons'  => '//code.ionicframework.com/ionicons/1.5.2/css/ionicons.min.css',
      'js'        => '/assets/js/scripts.js',
      'modernizr' => '/assets/vendor/modernizr/modernizr.js',
      'jquery'    => '//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.js'
    );
  } else {
    $get_assets = file_get_contents(get_template_directory() . '/assets/manifest.json');
    $assets     = json_decode($get_assets, true);
    $assets     = array(
      'css'       => '/assets/css/main.min.css?' . $assets['assets/css/main.min.css']['hash'],
      'ionicons'  => '//code.ionicframework.com/ionicons/1.5.2/css/ionicons.min.css',
      'js'        => '/assets/js/scripts.min.js?' . $assets['assets/js/scripts.min.js']['hash'],
      'modernizr' => '/assets/js/vendor/modernizr.min.js',
      'jquery'    => '//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js'
    );
  }

  wp_enqueue_style('roots_css', get_template_directory_uri() . $assets['css'], false, null);
  wp_enqueue_style('ionicons_css', $assets['ionicons'], false, null);

  if (is_single() && comments_open() && get_option('thread_comments')) {
    wp_enqueue_script('comment-reply');
  }

  wp_enqueue_script('modernizr', get_template_directory_uri() . $assets['modernizr'], array(), null, true);
  wp_enqueue_script('jquery');
  wp_enqueue_script('roots_js', get_template_directory_uri() . $assets['js'], array(), null, true);
}
add_action('wp_enqueue_scripts', 'roots_scripts', 100);

/**
 * Admin scripts
 */
function load_custom_wp_admin_style() {
  wp_enqueue_style('admin_css', get_template_directory_uri() . '/assets/css/admin.css', false, null);
  wp_enqueue_style('icons_css', get_template_directory_uri() . '/assets/css/icons.min.css', false, null);

  $js = get_template_directory_uri() . '/assets/js/icon-picker.js';
  wp_enqueue_script( 'dashicons-picker', $js, array( 'jquery' ), '1.0' );
}
global $pagenow;
if ($pagenow=="edit-tags.php" && isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] == 'category'  ) {
  add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_style' );
}


// http://wordpress.stackexchange.com/a/12450
function roots_jquery_local_fallback($src, $handle = null) {
  static $add_jquery_fallback = false;

  if ($add_jquery_fallback) {
    echo '<script>window.jQuery || document.write(\'<script src="' . get_template_directory_uri() . '/assets/vendor/jquery/dist/jquery.min.js?1.11.1"><\/script>\')</script>' . "\n";
    $add_jquery_fallback = false;
  }

  if ($handle === 'jquery') {
    $add_jquery_fallback = true;
  }

  return $src;
}
add_action('wp_head', 'roots_jquery_local_fallback');

/**
 * Google Analytics snippet from HTML5 Boilerplate
 *
 * Cookie domain is 'auto' configured. See: http://goo.gl/VUCHKM
 */
function roots_google_analytics() { ?>
<script>
  <?php if (WP_ENV === 'production') : ?>
    (function(b,o,i,l,e,r){b.GoogleAnalyticsObject=l;b[l]||(b[l]=
    function(){(b[l].q=b[l].q||[]).push(arguments)});b[l].l=+new Date;
    e=o.createElement(i);r=o.getElementsByTagName(i)[0];
    e.src='//www.google-analytics.com/analytics.js';
    r.parentNode.insertBefore(e,r)}(window,document,'script','ga'));
  <?php else : ?>
    function ga() {
      console.log('GoogleAnalytics: ' + [].slice.call(arguments));
    }
  <?php endif; ?>
  ga('create','<?php echo GOOGLE_ANALYTICS_ID; ?>','auto');ga('send','pageview');
</script>

<?php }
if (GOOGLE_ANALYTICS_ID && (WP_ENV !== 'production' || !current_user_can('manage_options'))) {
  add_action('wp_footer', 'roots_google_analytics', 20);
}
