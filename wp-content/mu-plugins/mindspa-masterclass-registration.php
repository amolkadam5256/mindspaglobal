<?php
/**
 * MindSpa Global masterclass registration form.
 */

defined( 'ABSPATH' ) || exit;

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

add_action( 'admin_post_nopriv_mindspa_register', 'mindspa_handle_registration' );
add_action( 'admin_post_mindspa_register', 'mindspa_handle_registration' );

function mindspa_handle_registration() {
	if ( ! isset( $_POST['mindspa_registration_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mindspa_registration_nonce'] ) ), 'mindspa_registration' ) ) {
		wp_die( 'Security check failed.', 'Registration error', array( 'response' => 403 ) );
	}

	$name  = mindspa_registration_value( 'full_name' );
	$email = sanitize_email( mindspa_registration_value( 'email' ) );
	$phone = mindspa_registration_value( 'phone' );
	$city  = mindspa_registration_value( 'city' );
	$profession = mindspa_registration_value( 'profession' );
	$goal = mindspa_registration_value( 'goal' );
	$mode = mindspa_registration_value( 'session_mode' );

	if ( ! $name || ! is_email( $email ) || ! $phone || ! $city || ! $profession || empty( $_POST['consent'] ) ) {
		wp_safe_redirect( add_query_arg( 'mindspa_registration', 'invalid', wp_get_referer() ?: home_url( '/' ) ) );
		exit;
	}

	$registration_id = wp_insert_post( array(
		'post_type'   => 'mindspa_registration',
		'post_status' => 'private',
		'post_title'  => $name . ' – ' . current_time( 'Y-m-d H:i' ),
		'meta_input'  => array(
			'email' => $email, 'phone' => $phone, 'city' => $city, 'profession' => $profession,
			'goal' => $goal, 'session_mode' => $mode,
			'consent' => 'Yes', 'source' => 'Neuro-Mind Gym Masterclass Landing Page',
			'utm_source' => mindspa_registration_value( 'utm_source' ),
			'utm_medium' => mindspa_registration_value( 'utm_medium' ),
			'utm_campaign' => mindspa_registration_value( 'utm_campaign' ),
		),
	) );

	$subject = 'New Neuro-Mind Gym Masterclass Registration – ' . $name;
	$message = "New registration received from the MindSpa Global website.\n\n" .
		"Full Name: {$name}\nEmail: {$email}\nPhone / WhatsApp: {$phone}\nCity: {$city}\n" .
		"Profession: {$profession}\nPrimary Interest: {$goal}\n" .
		"Session Mode: {$mode}\nConsent: Yes\nSubmission Date: " . current_time( 'mysql' ) .
		"\nSource: Neuro-Mind Gym Masterclass Landing Page";
	wp_mail( 'training.mindspaglobal@gmail.com', $subject, $message, array( 'Reply-To: ' . $email ) );

	wp_safe_redirect( add_query_arg( 'mindspa_registration', $registration_id ? 'success' : 'error', wp_get_referer() ?: home_url( '/' ) ) );
	exit;
}

function mindspa_registration_form() {
	$status = isset( $_GET['mindspa_registration'] ) ? sanitize_key( $_GET['mindspa_registration'] ) : '';
	$output = $status === 'success' ? '<p class="mindspa-registration-success">Thank you. Your registration enquiry has been received.</p>' : '';
	$output .= $status === 'invalid' ? '<p class="mindspa-registration-error">Please complete all required fields and provide a valid email address.</p>' : '';
	$output .= '<form class="mindspa-registration-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><h2>Reserve Your Seat</h2><p class="mindspa-registration-intro">Register your interest in the Neuro-Mind Gym Masterclass</p>';
	$output .= '<input type="hidden" name="action" value="mindspa_register"><input type="hidden" name="utm_source" value=""><input type="hidden" name="utm_medium" value=""><input type="hidden" name="utm_campaign" value="">';
	$output .= wp_nonce_field( 'mindspa_registration', 'mindspa_registration_nonce', true, false );
	$output .= '<p><label>Full Name *<input type="text" name="full_name" required></label></p><p><label>Email Address *<input type="email" name="email" required></label></p><p><label>Phone / WhatsApp Number *<input type="tel" name="phone" required></label></p><p><label>City / Location *<input type="text" name="city" required></label></p>';
	$output .= '<p><label>Profession *<select name="profession" required><option value="">Select profession</option><option>Working Professional</option><option>Business Owner / Entrepreneur</option><option>Student</option><option>Homemaker</option><option>Corporate Leader / HR</option><option>Other</option></select></label></p>';
	$output .= '<p><label>What would you most like to work on?<select name="goal"><option>Overthinking & Stress Management</option><option>Focus & Concentration</option><option>Confidence & Career Growth</option><option>Relationships & Emotional Wellbeing</option><option>Better Sleep & Relaxation</option><option>Other</option></select></label></p>';
	$output .= '<p><label>Preferred Session Mode<select name="session_mode"><option>Online via Zoom</option><option>In-person (if available)</option></select></label></p>';
	$output .= '<p><label><input type="checkbox" name="consent" value="yes" required> I agree to be contacted by MindSpa Global regarding this masterclass.</label></p><p><button type="submit">Register for Masterclass</button></p></form>';
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
		.mindspa-registration-form input[type="text"], .mindspa-registration-form input[type="email"], .mindspa-registration-form input[type="tel"], .mindspa-registration-form select { box-sizing: border-box; width: 100%; min-height: 40px; margin-top: 4px; padding: 8px 11px; border: 1px solid #d9d6e7; border-radius: 7px; background: #fbfaff; color: #242342; font: inherit; font-size: 13px; transition: border-color .2s, box-shadow .2s; }
		.mindspa-registration-form input:focus, .mindspa-registration-form select:focus { outline: none; border-color: #8477b0; box-shadow: 0 0 0 3px rgba(132, 119, 176, .18); }
		.mindspa-registration-form p { width: 100%; }
		.mindspa-registration-form p:last-child { margin-top: 16px; margin-bottom: 0; }
		.mindspa-registration-form p:last-child button { width: 100%; min-height: 44px; border: 0; border-radius: 7px; background: #302f68; color: #fff; font: inherit; font-size: 14px; font-weight: 700; cursor: pointer; transition: background .2s, transform .2s; }
		.mindspa-registration-form p:last-child button:hover { background: #8477b0; transform: translateY(-1px); }
		.mindspa-registration-form input[type="checkbox"] { accent-color: #302f68; margin-right: 8px; }
		.mindspa-registration-success, .mindspa-registration-error { max-width: 760px; margin: 24px auto; padding: 16px 20px; border-radius: 10px; font-weight: 600; }
		.mindspa-registration-success { background: #edf9f1; color: #176b35; }
		.mindspa-registration-error { background: #fff1f1; color: #a12626; }
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
			modal.querySelectorAll('form').forEach(function (form) {
				form.addEventListener('submit', function () {
					sessionStorage.setItem('mindspa_registration_popup_submitted', '1');
					close();
				});
			});
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
