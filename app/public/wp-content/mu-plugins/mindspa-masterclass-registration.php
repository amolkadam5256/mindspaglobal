<?php
/**
 * MindSpa Global contact section and session registration form.
 */

defined( 'ABSPATH' ) || exit;

/**
 * The registration form is required on the public landing pages even if the
 * host has not activated it in the regular plugins screen. require_once keeps
 * this safe when WordPress also loads it from the active plugins list.
 */
$registration_plugin = WP_PLUGIN_DIR . '/sudarshan-sabat-registration/sudarshan-sabat-registration.php';
if ( is_readable( $registration_plugin ) ) {
	require_once $registration_plugin;
}

/**
 * Keep public pages cacheable. PixelYourSite's PHP session starts on every
 * frontend request by default, which sends PHPSESSID and no-cache headers.
 * Its browser-based tracking remains active without that session.
 */
add_action( 'plugins_loaded', function () {
	if ( function_exists( '\\PixelYourSite\\PYS' ) ) {
		remove_action( 'wp', array( \PixelYourSite\PYS(), 'controllSessionStart' ), 10 );
	}
}, 100 );

add_filter( 'the_content', function ( $content ) {
	$contact_url = is_page( 1032 ) ? get_permalink( 1032 ) . '#contact' : home_url( '/#contact' );
	$contact_url = esc_url( $contact_url );
	$content     = preg_replace( '/href=(["\'])https?:\/\/rzp\.io\/[^"\']+\1/i', 'href=$1' . $contact_url . '$1', $content );
	$content     = preg_replace( '/href=(["\'])https?:\/\/example\.com\/register\/?\1/i', 'href=$1' . $contact_url . '$1', $content );
	return str_replace( 'href="#mindspa-registration-modal"', 'href="' . $contact_url . '"', $content );
}, 20 );

add_filter( 'the_content', function ( $content ) {
	static $contact_section_added = false;
	if ( is_admin() || ( ! is_front_page() && ! is_page( 1032 ) ) || $contact_section_added || has_shortcode( $content, 'ss_registration_form' ) ) {
		return $content;
	}
	$contact_section_added = true;
	return $content . '<section id="contact" class="mindspa-registration-section" aria-label="Register for the two-day group session">' . do_shortcode( '[ss_registration_form]' ) . '</section>';
} );

/**
 * Elementor's canvas template omits the theme footer. Add a lightweight,
 * accessible footer to the public landing page without changing the theme.
 */
add_action( 'wp_footer', function () {
	if ( is_admin() || ( ! is_front_page() && ! is_page( 1032 ) ) ) {
		return;
	}

	$home_url         = home_url( '/' );
	$registration_url = is_page( 1032 ) ? get_permalink( 1032 ) . '#contact' : home_url( '/#contact' );
	$contact_url      = 'mailto:training.mindspaglobal@gmail.com';
	?>
	<footer class="mindspa-site-footer" role="contentinfo">
		<div class="mindspa-site-footer__grid">
			<section class="mindspa-site-footer__brand" aria-label="About MindSpa Global">
				<a class="mindspa-site-footer__name" href="<?php echo esc_url( $home_url ); ?>">MindSpa Global</a>
				<p>Evidence-informed psychology, mindfulness and Neuro-Mind Reprogramming support for meaningful personal growth.</p>
			</section>
			<nav class="mindspa-site-footer__links" aria-label="Footer navigation">
				<h2>Explore</h2>
				<a href="<?php echo esc_url( $home_url ); ?>">Home</a>
			<a href="<?php echo esc_url( $registration_url ); ?>">Masterclass registration</a>
				<a href="<?php echo esc_url( $contact_url ); ?>">Contact us</a>
			</nav>
			<section class="mindspa-site-footer__contact" aria-label="Contact details">
				<h2>Get in touch</h2>
				<p>For session and masterclass enquiries:</p>
				<a href="<?php echo esc_url( $contact_url ); ?>">training.mindspaglobal@gmail.com</a>
			</section>
		</div>
		<div class="mindspa-site-footer__bottom">
			<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> MindSpa Global. All rights reserved.</p>
		</div>
	</footer>
	<style>
		.mindspa-site-footer { margin-top: 0; background: #242342; color: #f5f3fb; font-family: inherit; }
		.mindspa-site-footer__grid { display: grid; grid-template-columns: minmax(0, 1.5fr) repeat(2, minmax(0, 1fr)); gap: 42px; width: min(1120px, calc(100% - 40px)); margin: 0 auto; padding: 56px 0 44px; }
		.mindspa-site-footer__name { display: inline-block; margin-bottom: 12px; color: #fff; font-size: 24px; font-weight: 700; line-height: 1.2; text-decoration: none; }
		.mindspa-site-footer p { margin: 0; color: #d8d4e6; font-size: 15px; line-height: 1.7; }
		.mindspa-site-footer h2 { margin: 0 0 14px; color: #fff; font-size: 16px; line-height: 1.3; }
		.mindspa-site-footer__links { display: grid; align-content: start; gap: 9px; }
		.mindspa-site-footer a { color: #f5f3fb; text-underline-offset: 3px; }
		.mindspa-site-footer a:hover, .mindspa-site-footer a:focus { color: #f7b75b; }
		.mindspa-site-footer__contact { display: grid; align-content: start; gap: 10px; }
		.mindspa-site-footer__bottom { border-top: 1px solid rgba(255, 255, 255, .16); }
		.mindspa-site-footer__bottom p { width: min(1120px, calc(100% - 40px)); margin: 0 auto; padding: 18px 0; font-size: 13px; }
		@media (max-width: 720px) { .mindspa-site-footer__grid { grid-template-columns: 1fr; gap: 30px; padding: 42px 0 34px; } }
	</style>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			var contactUrl = <?php echo wp_json_encode( $registration_url ); ?>;
		document.querySelectorAll('a[href*="rzp.io"], a[href*="example.com/register"], a[href="#mindspa-registration-modal"], [data-mindspa-open-registration]').forEach(function (link) {
				link.setAttribute('href', contactUrl);
				link.setAttribute('target', '_self');
				link.removeAttribute('rel');
				link.removeAttribute('data-mindspa-open-registration');
			});
		});
	</script>
	<?php
} );
