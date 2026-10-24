<?php
/**
 * MindSpa Global masterclass registration form.
 */

defined( 'ABSPATH' ) || exit;

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

add_action( 'init', function () {
	register_post_type( 'mindspa_registration', array(
		'labels'       => array( 'name' => 'Masterclass Registrations', 'singular_name' => 'Masterclass Registration' ),
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => true,
		'supports'     => array( 'title', 'custom-fields' ),
		'menu_icon'    => 'dashicons-clipboard',
	) );
} );

function mindspa_registration_value( $key ) {
	return isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
}

/**
 * Supply the real group invite in wp-config.php, for example:
 * define( 'MINDSPA_MASTERCLASS_WHATSAPP_GROUP_URL', 'https://chat.whatsapp.com/your-real-invite-code' );
 *
 * A filter is also available for installations that keep this setting elsewhere.
 */
function mindspa_masterclass_whatsapp_group_url() {
	$url = defined( 'MINDSPA_MASTERCLASS_WHATSAPP_GROUP_URL' ) ? MINDSPA_MASTERCLASS_WHATSAPP_GROUP_URL : '';

	return esc_url( apply_filters( 'mindspa_masterclass_whatsapp_group_url', $url ) );
}

add_action( 'admin_post_nopriv_mindspa_register', 'mindspa_handle_registration' );
add_action( 'admin_post_mindspa_register', 'mindspa_handle_registration' );

function mindspa_handle_registration() {
	if ( ! isset( $_POST['mindspa_registration_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mindspa_registration_nonce'] ) ), 'mindspa_registration' ) ) {
		wp_die( 'Security check failed.', 'Registration error', array( 'response' => 403 ) );
	}

	$name       = mindspa_registration_value( 'full_name' );
	$phone      = mindspa_registration_value( 'phone' );
	$city       = mindspa_registration_value( 'city' );
	$profession = mindspa_registration_value( 'profession' );

	if ( ! $name || ! $phone || ! $city ) {
		wp_safe_redirect( add_query_arg( 'mindspa_registration', 'invalid', wp_get_referer() ?: home_url( '/' ) ) );
		exit;
	}

	$registration_id = wp_insert_post( array(
		'post_type'   => 'mindspa_registration',
		'post_status' => 'private',
		'post_title'  => $name . ' – ' . current_time( 'Y-m-d H:i' ),
		'meta_input'  => array(
			'phone' => $phone, 'city' => $city, 'profession' => $profession,
			'source' => 'Neuro-Mind Gym Masterclass Landing Page',
			'utm_source' => mindspa_registration_value( 'utm_source' ),
			'utm_medium' => mindspa_registration_value( 'utm_medium' ),
			'utm_campaign' => mindspa_registration_value( 'utm_campaign' ),
		),
	) );

	$subject = 'New Neuro-Mind Gym Masterclass Registration – ' . $name;
	$message = "New registration received from the MindSpa Global website.\n\n" .
		"Full Name: {$name}\nPhone / WhatsApp: {$phone}\nCity: {$city}\n" .
		"Profession: " . ( $profession ?: 'Not provided' ) . "\nSubmission Date: " . current_time( 'mysql' ) .
		"\nSource: Neuro-Mind Gym Masterclass Landing Page";
	wp_mail( 'training.mindspaglobal@gmail.com', $subject, $message );

	wp_safe_redirect( add_query_arg( 'mindspa_registration', $registration_id ? 'success' : 'error', wp_get_referer() ?: home_url( '/' ) ) );
	exit;
}

function mindspa_registration_form() {
	$status    = isset( $_GET['mindspa_registration'] ) ? sanitize_key( $_GET['mindspa_registration'] ) : '';
	$group_url = mindspa_masterclass_whatsapp_group_url();
	$output    = '';
	if ( $status === 'success' ) {
		$output .= '<div class="mindspa-registration-success"><p>Thank you. Your registration has been received.</p>';
		if ( $group_url ) {
			$output .= '<a class="mindspa-whatsapp-group-button" href="' . $group_url . '" target="_blank" rel="noopener noreferrer">Join WhatsApp Group</a>';
		}
		$output .= '</div>';
		return $output;
	}
	$output .= $status === 'invalid' ? '<p class="mindspa-registration-error">Please complete all required fields.</p>' : '';
	$output .= '<form class="mindspa-registration-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><h2>Reserve Your Seat</h2><p class="mindspa-registration-intro">Register your interest in the Neuro-Mind Gym Masterclass</p>';
	$output .= '<input type="hidden" name="action" value="mindspa_register"><input type="hidden" name="utm_source" value=""><input type="hidden" name="utm_medium" value=""><input type="hidden" name="utm_campaign" value="">';
	$output .= wp_nonce_field( 'mindspa_registration', 'mindspa_registration_nonce', true, false );
	$output .= '<p><label>Full Name *<input type="text" name="full_name" autocomplete="name" required></label></p><p><label>WhatsApp Number *<input type="tel" name="phone" autocomplete="tel" inputmode="tel" required></label></p><p><label>City / Location *<input type="text" name="city" autocomplete="address-level2" required></label></p>';
	$output .= '<p><label>Profession <span class="mindspa-registration-optional">(optional)</span><select name="profession" autocomplete="organization-title"><option value="">Select profession</option><option>Working Professional</option><option>Business Owner / Entrepreneur</option><option>Student</option><option>Homemaker</option><option>Corporate Leader / HR</option><option>Other</option></select></label></p>';
	$output .= '<p><button type="submit">Register Now</button></p></form>';
	return $output;
}
add_shortcode( 'mindspa_masterclass_registration', 'mindspa_registration_form' );

add_filter( 'the_content', function ( $content ) {
	$contact_url = esc_url( home_url( '/#contact' ) );
	return preg_replace( '/href=(["\'])https?:\/\/rzp\.io\/[^"\']+\1/i', 'href=$1' . $contact_url . '$1', $content );
}, 20 );

add_filter( 'the_content', function ( $content ) {
	if ( is_admin() || ! is_singular( 'page' ) || ! preg_match( '/neuro|masterclass/i', get_the_title() . ' ' . $content ) || has_shortcode( $content, 'mindspa_masterclass_registration' ) ) {
		return $content;
	}
	return $content . do_shortcode( '[mindspa_masterclass_registration]' );
} );

add_action( 'wp_head', function () {
	if ( ! is_singular( 'page' ) ) {
		return;
	}
	?>
	<style>
		.mindspa-registration-form { width: 100%; max-width: 620px; box-sizing: border-box; margin: 20px auto; padding: 26px 30px; background: #fff; border: 1px solid #e7e4f0; border-radius: 16px; box-shadow: 0 16px 45px rgba(48, 47, 104, .10); color: #242342; }
		.mindspa-registration-form h2 { margin: 0 0 5px; color: #302f68; font-size: clamp(25px, 4vw, 32px); line-height: 1.2; }
		.mindspa-registration-intro { margin-bottom: 28px !important; color: #6d6b82; font-size: 15px; }
		.mindspa-registration-form p { margin: 0 0 12px; }
		.mindspa-registration-form label { display: block; color: #302f68; font-size: 12px; font-weight: 600; }
		.mindspa-registration-form input[type="text"], .mindspa-registration-form input[type="tel"], .mindspa-registration-form select { box-sizing: border-box; width: 100%; min-height: 44px; margin-top: 4px; padding: 9px 11px; border: 1px solid #d9d6e7; border-radius: 7px; background: #fbfaff; color: #242342; font: inherit; font-size: 16px; transition: border-color .2s, box-shadow .2s; }
		.mindspa-registration-form input:focus, .mindspa-registration-form select:focus { outline: none; border-color: #8477b0; box-shadow: 0 0 0 3px rgba(132, 119, 176, .18); }
		.mindspa-registration-optional { color: #6d6b82; font-weight: 400; }
		.mindspa-registration-form p { width: 100%; }
		.mindspa-registration-form p:last-child { margin-top: 16px; margin-bottom: 0; }
		.mindspa-registration-form p:last-child button { width: 100%; min-height: 44px; border: 0; border-radius: 7px; background: #302f68; color: #fff; font: inherit; font-size: 14px; font-weight: 700; cursor: pointer; transition: background .2s, transform .2s; }
		.mindspa-registration-form p:last-child button:hover { background: #8477b0; transform: translateY(-1px); }
		.mindspa-registration-form input[type="checkbox"] { accent-color: #302f68; margin-right: 8px; }
		.mindspa-registration-success, .mindspa-registration-error { max-width: 620px; box-sizing: border-box; margin: 24px auto; padding: 16px 20px; border-radius: 10px; font-weight: 600; }
		.mindspa-registration-success p { margin: 0 0 14px; }
		.mindspa-registration-success { background: #edf9f1; color: #176b35; }
		.mindspa-registration-error { background: #fff1f1; color: #a12626; }
		.mindspa-whatsapp-group-button { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 0 18px; border-radius: 7px; background: #302f68; color: #fff !important; font-size: 14px; font-weight: 700; text-decoration: none; }
		.mindspa-whatsapp-group-button:hover, .mindspa-whatsapp-group-button:focus { background: #8477b0; color: #fff !important; }
		@media (max-width: 640px) { .mindspa-registration-form { margin: 10px auto; padding: 22px 18px; border-radius: 14px; } }
	</style>
	<?php
} );

add_action( 'wp_footer', function () {
	if ( is_admin() ) {
		return;
	}
	?>
	<div class="mindspa-registration-modal" id="mindspa-registration-modal" role="dialog" aria-modal="true" aria-labelledby="mindspa-modal-title" hidden>
		<div class="mindspa-registration-overlay" data-mindspa-close></div>
		<div class="mindspa-registration-dialog">
			<button class="mindspa-registration-close" type="button" aria-label="Close registration form" data-mindspa-close>&times;</button>
			<div id="mindspa-modal-title"><?php echo do_shortcode( '[mindspa_masterclass_registration]' ); ?></div>
		</div>
	</div>
	<script>
			document.addEventListener('DOMContentLoaded', function () {
				document.querySelectorAll('a[href*="rzp.io"]').forEach(function (link) {
					link.setAttribute('href', '<?php echo esc_url( home_url( '/#contact' ) ); ?>');
					link.setAttribute('target', '_self');
					link.removeAttribute('rel');
				});
			var modal = document.getElementById('mindspa-registration-modal');
			if (!modal) return;
			// The popup belongs to the landing-page hero, so do not show it on
			// pages that do not use the hero component.
			var hero = document.querySelector('.neuro-hero');
			if (!hero) return;
			var close = function () { modal.hidden = true; document.body.classList.remove('mindspa-modal-open'); };
			modal.querySelectorAll('[data-mindspa-close]').forEach(function (button) { button.addEventListener('click', close); });
			document.addEventListener('keydown', function (event) { if (event.key === 'Escape') close(); });
			if (sessionStorage.getItem('mindspa_registration_popup_submitted')) {
				sessionStorage.removeItem('mindspa_registration_popup_submitted');
				return;
			}
			setTimeout(function () { modal.hidden = false; document.body.classList.add('mindspa-modal-open'); }, 1500);
		});
	</script>
	<style>
		.mindspa-modal-open { overflow: hidden; }
		.mindspa-registration-modal[hidden] { display: none; }
		.mindspa-registration-modal { position: fixed; inset: 0; z-index: 99999; display: grid; place-items: center; padding: 20px; }
		.mindspa-registration-overlay { position: absolute; inset: 0; background: rgba(24, 22, 55, .68); backdrop-filter: blur(4px); }
		.mindspa-registration-dialog { position: relative; z-index: 1; width: min(620px, calc(100vw - 24px)); max-height: calc(100vh - 24px); overflow-y: auto; margin: 0 auto; }
		.mindspa-registration-dialog .mindspa-registration-form { margin: 0; }
		.mindspa-registration-close { position: absolute; top: 12px; right: 18px; z-index: 2; width: 38px; height: 38px; border: 0; border-radius: 50%; background: #eeecfa; color: #302f68; font-size: 28px; line-height: 1; cursor: pointer; }
		.mindspa-registration-close:hover { background: #302f68; color: #fff; }
		@media (max-width: 640px) { .mindspa-registration-modal { padding: 10px; } .mindspa-registration-dialog { max-height: 96vh; } }
	</style>
	<?php
} );

/**
 * Elementor's canvas template omits the theme footer. Add a lightweight,
 * accessible footer to the public landing page without changing the theme.
 */
add_action( 'wp_footer', function () {
	if ( is_admin() || ! is_front_page() ) {
		return;
	}

	$home_url    = home_url( '/' );
	$contact_url = 'mailto:training.mindspaglobal@gmail.com';
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
				<a href="#mindspa-registration-modal" data-mindspa-footer-register>Masterclass registration</a>
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
			var registerLink = document.querySelector('[data-mindspa-footer-register]');
			var modal = document.getElementById('mindspa-registration-modal');
			if (!registerLink || !modal) return;
			registerLink.addEventListener('click', function (event) {
				event.preventDefault();
				modal.hidden = false;
				document.body.classList.add('mindspa-modal-open');
			});
		});
	</script>
	<?php
} );
