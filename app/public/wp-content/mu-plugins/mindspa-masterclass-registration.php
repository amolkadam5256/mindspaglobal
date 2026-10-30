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
	return $content;
}, 20 );

/**
 * Keep the workshop details consistent on the two public landing pages.
 * The page content is maintained in Elementor/WordPress, so make these
 * presentation updates as it renders rather than changing a database dump.
 */
function mindspa_update_workshop_details( $content ) {
	if ( is_admin() || ! is_page() ) {
		return $content;
	}

	$content = str_ireplace(
		array(
			'<small>DATE</small>',
			'9th &amp; 10th October <br>(Sat &amp; Sun)',
			'9th & 10th October <br>(Sat & Sun)',
			'9th &amp; 10th October<br>(Sat &amp; Sun)',
			'9th & 10th October<br>(Sat & Sun)',
			'9th &amp; 10th October',
			'9th & 10th October',
			'7:00pm - 8:00pm',
			'7:00 PM - 8:00 PM',
			'12 PM - 2 PM',
			'12 PM–2 PM',
			'Join 2 Days Masterclass',
			'2 Days Masterclass',
			'NEURO-MIND GYM MASTERCLASS (2 Days)',
			'NEURO-MIND GYM MASTERCLASS<br>(2 Days)',
			'NEURO-MIND MASTERCLASS  (2 Days)',
			'NEURO-MIND MASTERCLASS (2 Days)',
			'2 Days Scientific Masterclass – Neuro Mind GYM',
			'2 Hours',
			'Online (Zoom)',
			'<span>English</span>',
		),
		array(
			'<small>SCHEDULE</small>',
			'Every Saturday &amp; Sunday',
			'Every Saturday &amp; Sunday',
			'Every Saturday &amp; Sunday',
			'Every Saturday &amp; Sunday',
			'Every Saturday &amp; Sunday',
			'Every Saturday &amp; Sunday',
			'7:00pm - 8:30pm',
			'7:00 PM - 8:30 PM',
			'12 PM - 1:30 PM',
			'12 PM–1:30 PM',
			'Join the 90-Minute Masterclass',
			'90-Minute Masterclass',
			'NEURO-MIND GYM MASTERCLASS (90 Minutes)',
			'NEURO-MIND GYM MASTERCLASS<br>(90 Minutes)',
			'NEURO-MIND MASTERCLASS (90 Minutes)',
			'NEURO-MIND MASTERCLASS (90 Minutes)',
			'90-Minute Scientific Masterclass – Neuro Mind GYM',
			'90 Minutes',
			'Online via Zoom',
			'<span>English, Hindi &amp; Marathi</span>',
		),
		$content
	);
	$content = str_ireplace(
		array( 'फक्त २ दिवसांत', '२ दिवसानंतरच', '२ दिवसांत', '२ दिवस' ),
		array( 'फक्त ९० मिनिटांत', '९० मिनिटांनंतरच', '९० मिनिटांत', '९० मिनिटे' ),
		$content
	);
	$content = preg_replace( '/\b2\s*[- ]?days?\b/i', '90 minutes', $content );
	$content = preg_replace( '/\btwo\s*[- ]?days?\b/i', '90 minutes', $content );
	$content = preg_replace( '/\btwo\s*[- ]?day\b/i', '90-minute', $content );

	// The masterclass page does not currently have a duration row.
	if ( is_page( 1032 ) && false === stripos( $content, '<small>DURATION</small>' ) ) {
		$content = preg_replace(
			'/(7:00pm\s*-\s*8:30pm)(\s*(?:🎥|<small>WHERE<\/small>))/u',
			'$1<br>⏳ <small>DURATION</small> 90 Minutes$2',
			$content,
			1
		);
	}

	return $content;
}
add_filter( 'the_content', 'mindspa_update_workshop_details', 25 );
add_filter( 'elementor/widget/render_content', 'mindspa_update_workshop_details', 25 );
add_filter( 'the_title', 'mindspa_update_workshop_details', 25 );

add_filter( 'the_content', function ( $content ) {
	static $contact_section_added = false;
	if ( is_admin() || ( ! is_front_page() && ! is_page( 1032 ) ) || $contact_section_added ) {
		return $content;
	}
	$contact_section_added = true;
	if ( has_shortcode( $content, 'ss_registration_form' ) ) {
		return do_shortcode( $content );
	}
	return $content . '<section id="contact" class="mindspa-registration-section" aria-label="Register for the 90-minute free group session">' . do_shortcode( '[ss_registration_form]' ) . '</section>';
} );

/**
 * Replace any shortcode text left behind by page-builder rendering on the two
 * public registration pages. Core normally handles it at priority 11; this
 * late pass covers builder output generated after that filter has run.
 */
add_filter( 'the_content', function ( $content ) {
	if ( is_admin() || ( ! is_front_page() && ! is_page( 1032 ) ) || ! shortcode_exists( 'ss_registration_form' ) ) {
		return $content;
	}
	return str_replace( '[ss_registration_form]', do_shortcode( '[ss_registration_form]' ), $content );
}, 99 );

/**
 * Elementor's canvas template omits the theme footer. Add a lightweight,
 * accessible footer to the public landing page without changing the theme.
 */
add_action( 'wp_footer', function () {
	if ( is_admin() || ! is_page() ) {
		return;
	}

	$show_landing_footer = is_front_page() || is_page( 1032 );
	$home_url         = home_url( '/' );
	$registration_url = is_page( 1032 ) ? get_permalink( 1032 ) . '#contact' : home_url( '/#contact' );
	$contact_url      = 'mailto:training.mindspaglobal@gmail.com';
	?>
	<?php if ( $show_landing_footer ) : ?>
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
	<?php endif; ?>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			if (<?php echo $show_landing_footer ? 'true' : 'false'; ?>) {
				var contactUrl = <?php echo wp_json_encode( $registration_url ); ?>;
				document.querySelectorAll('a[href*="rzp.io"], a[href*="example.com/register"]').forEach(function (link) {
					link.setAttribute('href', contactUrl);
					link.setAttribute('target', '_self');
					link.removeAttribute('rel');
				});
			}

			var workshopValues = {
				schedule: 'Every Saturday & Sunday',
				time: '12 PM - 1:30 PM',
				duration: '90 Minutes',
				language: 'English, Hindi & Marathi',
				mode: 'Online via Zoom'
			};
		document.querySelectorAll('.pi-card, .badge, .details-card').forEach(function (card) {
				var label = card.querySelector('small');
				var labelText = label ? label.textContent.trim().toUpperCase() : card.textContent.trim().toUpperCase();
				var key = null;
				if (/(DATE|SCHEDULE)/.test(labelText)) key = 'schedule';
				else if (/TIME/.test(labelText)) key = 'time';
				else if (/(DURATION)/.test(labelText)) key = 'duration';
				else if (/LANGUAGE/.test(labelText)) key = 'language';
				else if (/(MODE|WHERE)/.test(labelText)) key = 'mode';
				if (!key) return;

				if (key === 'schedule' && label) label.textContent = 'SCHEDULE';
				var value = card.querySelector('span');
				if (value) value.textContent = workshopValues[key];
			});

			var textWalker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
			var textNode;
			while ((textNode = textWalker.nextNode())) {
				textNode.nodeValue = textNode.nodeValue
					.replace(/NEURO-MIND GYM MASTERCLASS\s*\(2 Days\)/gi, 'NEURO-MIND GYM MASTERCLASS (90 Minutes)')
					.replace(/फक्त\s*२\s*दिवसांत/g, 'फक्त ९० मिनिटांत')
					.replace(/२\s*दिवसानंतरच/g, '९० मिनिटांनंतरच')
					.replace(/२\s*दिवसांत/g, '९० मिनिटांत')
					.replace(/२\s*दिवस/g, '९० मिनिटे')
					.replace(/\b2\s*[- ]?days?\b/gi, '90 minutes')
					.replace(/\btwo\s*[- ]?days?\b/gi, '90 minutes')
					.replace(/\btwo\s*[- ]?day\b/gi, '90-minute');
			}
		});
	</script>
	<?php
} );
