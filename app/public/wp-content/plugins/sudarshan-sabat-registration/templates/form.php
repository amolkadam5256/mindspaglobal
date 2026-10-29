<?php
/**
 * Registration form template — Dr. Dipti Panhalkar Session Booking
 * Rendered by the [ss_registration_form] shortcode.
 *
 * @package Sudarshan_Sabat_Registration
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Use WordPress built-in privacy policy URL (falls back to # if not set).
$privacy_url = function_exists( 'get_privacy_policy_url' ) ? get_privacy_policy_url() : '#';
?>

<div class="ss-reg-wrapper" id="ss-reg-wrapper">

    <!-- ═══════════════════════════════════════
         FORM PANEL
    ════════════════════════════════════════ -->
    <div class="ss-reg-card" id="ss-reg-form-panel">

        <!-- ── Header ───────────────────────────── -->
        <header class="ss-reg-header">

            <!-- Doctor name — gold headline -->
            <h2 class="ss-reg-title">
                <?php esc_html_e( 'Join the Two-Day Group Session with', 'ss-registration' ); ?><br>
                <span class="ss-reg-doctor-name">Dr. Dipti Panhalkar</span>
            </h2>

            <!-- Session types row -->
            <div class="ss-reg-session-row" aria-label="<?php esc_attr_e( 'Two-day group session', 'ss-registration' ); ?>">
                <span class="ss-reg-session-pill ss-reg-session-pill--group">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <?php esc_html_e( 'Two-Day Group Session', 'ss-registration' ); ?>
                </span>
            </div>
            <p class="ss-reg-session-meta">
                <?php esc_html_e( 'Total duration: 2 hours · Online via Zoom', 'ss-registration' ); ?>
            </p>

        </header><!-- /.ss-reg-header -->

        <!-- ── Alerts ───────────────────────────── -->
        <noscript>
            <p class="ss-reg-notice ss-reg-notice--error">
                <?php esc_html_e( 'Please enable JavaScript to use this form.', 'ss-registration' ); ?>
            </p>
        </noscript>
        <div class="ss-reg-notice" id="ss-reg-notice" role="alert" aria-live="polite" hidden></div>

        <!-- ── Form ─────────────────────────────── -->
        <form
            id="ss-reg-form"
            class="ss-reg-form"
            novalidate
            aria-label="<?php esc_attr_e( 'Dr. Dipti Panhalkar Session Registration Form', 'ss-registration' ); ?>"
        >
            <?php wp_nonce_field( 'ss_registration_nonce', 'ss_nonce' ); ?>

            <!-- ── Personal Details Box ───────────── -->
            <div class="ss-reg-fields-box">

                <p class="ss-reg-section-label">
                    <?php esc_html_e( 'Your Details', 'ss-registration' ); ?>
                </p>

                <div class="ss-reg-grid">

                    <!-- 1. Full Name -->
                    <div class="ss-reg-field">
                        <label for="ss-full-name" class="ss-reg-label">
                            <?php esc_html_e( 'Full Name', 'ss-registration' ); ?>
                            <span class="ss-reg-required" aria-hidden="true">*</span>
                        </label>
                        <input
                            type="text"
                            id="ss-full-name"
                            name="full_name"
                            class="ss-reg-input"
                            placeholder="<?php esc_attr_e( 'Enter your full name', 'ss-registration' ); ?>"
                            required
                            autocomplete="name"
                            aria-required="true"
                            aria-describedby="ss-full-name-error"
                        />
                        <span class="ss-reg-field-error" id="ss-full-name-error" role="alert"></span>
                    </div>

                    <!-- 2. Phone Number -->
                    <div class="ss-reg-field">
                        <label for="ss-phone" class="ss-reg-label">
                            <?php esc_html_e( 'Phone No.', 'ss-registration' ); ?>
                            <span class="ss-reg-required" aria-hidden="true">*</span>
                        </label>
                        <input
                            type="tel"
                            id="ss-phone"
                            name="phone"
                            class="ss-reg-input"
                            placeholder="<?php esc_attr_e( 'e.g. +91 98765 43210', 'ss-registration' ); ?>"
                            required
                            autocomplete="tel"
                            aria-required="true"
                            aria-describedby="ss-phone-error"
                            pattern="[\+]?[0-9\s\-\(\)]{7,20}"
                        />
                        <span class="ss-reg-field-error" id="ss-phone-error" role="alert"></span>
                    </div>

                    <!-- 3. Profession Dropdown -->
                    <div class="ss-reg-field">
                        <label for="ss-profession" class="ss-reg-label">
                            <?php esc_html_e( 'Select Your Profession', 'ss-registration' ); ?>
                            <span class="ss-reg-required" aria-hidden="true">*</span>
                        </label>
                        <div class="ss-reg-select-wrap">
                            <select
                                id="ss-profession"
                                name="profession"
                                class="ss-reg-input ss-reg-select"
                                required
                                aria-required="true"
                                aria-describedby="ss-profession-error"
                            >
                                <option value="" disabled selected><?php esc_html_e( 'Select your profession', 'ss-registration' ); ?></option>
                                <option value="Business Owner"><?php esc_html_e( 'Business Owner', 'ss-registration' ); ?></option>
                                <option value="Working Professional"><?php esc_html_e( 'Working Professional', 'ss-registration' ); ?></option>
                                <option value="Trainer or Coach"><?php esc_html_e( 'Trainer or Coach', 'ss-registration' ); ?></option>
                                <option value="Student"><?php esc_html_e( 'Student', 'ss-registration' ); ?></option>
                                <option value="Entrepreneur"><?php esc_html_e( 'Entrepreneur', 'ss-registration' ); ?></option>
                                <option value="Freelancer"><?php esc_html_e( 'Freelancer', 'ss-registration' ); ?></option>
                                <option value="Other"><?php esc_html_e( 'Other', 'ss-registration' ); ?></option>
                            </select>
                            <span class="ss-reg-select-arrow" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                            </span>
                        </div>
                        <span class="ss-reg-field-error" id="ss-profession-error" role="alert"></span>
                    </div>

                    <!-- 4. Location -->
                    <div class="ss-reg-field">
                        <label for="ss-location" class="ss-reg-label">
                            <?php esc_html_e( 'Location', 'ss-registration' ); ?>
                            <span class="ss-reg-required" aria-hidden="true">*</span>
                        </label>
                        <input
                            type="text"
                            id="ss-location"
                            name="location"
                            class="ss-reg-input"
                            placeholder="<?php esc_attr_e( 'Your city / location', 'ss-registration' ); ?>"
                            required
                            autocomplete="address-level2"
                            aria-required="true"
                            aria-describedby="ss-location-error"
                        />
                        <span class="ss-reg-field-error" id="ss-location-error" role="alert"></span>
                    </div>

                </div><!-- /.ss-reg-grid -->
            </div><!-- /.ss-reg-fields-box -->

            <!-- ── Session Interest Question ────── -->
            <div class="ss-reg-question-box">
                <fieldset class="ss-reg-fieldset">
                    <legend class="ss-reg-legend">
                        <?php esc_html_e( 'How serious are you about joining the', 'ss-registration' ); ?>
                        <strong><?php esc_html_e( 'two-day group session', 'ss-registration' ); ?></strong>?
                        <span class="ss-reg-required" aria-hidden="true">*</span>
                    </legend>

                    <div class="ss-reg-radio-group" role="group">

                        <label class="ss-reg-radio-label" for="ss-serious-yes">
                            <input type="radio" id="ss-serious-yes" name="seriousness" value="YES" class="ss-reg-radio" required />
                            <span class="ss-reg-radio-btn">
                                <span class="ss-reg-radio-indicator" aria-hidden="true"></span>
                                <span class="ss-reg-radio-text">
                                    <span class="ss-reg-radio-main"><?php esc_html_e( 'Yes', 'ss-registration' ); ?></span>
                                    <span class="ss-reg-radio-sub"><?php esc_html_e( "I'm ready to book", 'ss-registration' ); ?></span>
                                </span>
                            </span>
                        </label>

                        <label class="ss-reg-radio-label" for="ss-serious-no">
                            <input type="radio" id="ss-serious-no" name="seriousness" value="NO" class="ss-reg-radio" />
                            <span class="ss-reg-radio-btn">
                                <span class="ss-reg-radio-indicator" aria-hidden="true"></span>
                                <span class="ss-reg-radio-text">
                                    <span class="ss-reg-radio-main"><?php esc_html_e( 'No', 'ss-registration' ); ?></span>
                                    <span class="ss-reg-radio-sub"><?php esc_html_e( 'Just exploring', 'ss-registration' ); ?></span>
                                </span>
                            </span>
                        </label>

                        <label class="ss-reg-radio-label" for="ss-serious-other">
                            <input type="radio" id="ss-serious-other" name="seriousness" value="OTHER" class="ss-reg-radio" />
                            <span class="ss-reg-radio-btn">
                                <span class="ss-reg-radio-indicator" aria-hidden="true"></span>
                                <span class="ss-reg-radio-text">
                                    <span class="ss-reg-radio-main"><?php esc_html_e( 'Other', 'ss-registration' ); ?></span>
                                    <span class="ss-reg-radio-sub"><?php esc_html_e( "I'll decide later", 'ss-registration' ); ?></span>
                                </span>
                            </span>
                        </label>

                    </div>
                    <span class="ss-reg-field-error" id="ss-seriousness-error" role="alert"></span>
                </fieldset>
            </div><!-- /.ss-reg-question-box -->

            <!-- ── Submit Button ─────────────────── -->
            <button
                type="submit"
                id="ss-reg-submit"
                class="ss-reg-submit"
                aria-describedby="ss-reg-privacy"
            >
                <span class="ss-reg-submit-text"><?php esc_html_e( 'Submit', 'ss-registration' ); ?></span>
                <span class="ss-reg-spinner" aria-hidden="true" hidden></span>
            </button>

            <!-- ── Privacy Notice ─────────────────── -->
            <p class="ss-reg-privacy" id="ss-reg-privacy">
                <?php
                printf(
                    /* translators: %1$s = opening <a> tag, %2$s = closing </a> tag */
                    esc_html__( 'By submitting this form, you agree to our %1$sPrivacy Policy%2$s and consent to being contacted regarding your session enquiry.', 'ss-registration' ),
                    '<a href="' . esc_url( $privacy_url ) . '" class="ss-reg-privacy-link" target="_blank" rel="noopener noreferrer">',
                    '</a>'
                );
                ?>
            </p>

        </form>
    </div><!-- /.ss-reg-card (form panel) -->


    <!-- ═══════════════════════════════════════
         THANK-YOU PANEL  (hidden until submit)
    ════════════════════════════════════════ -->
    <div class="ss-reg-card ss-reg-thankyou" id="ss-reg-thankyou-panel" hidden aria-live="polite">

        <!-- Success checkmark -->
        <div class="ss-reg-thankyou-icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        </div>

        <!-- Thank-you headline -->
        <h2 class="ss-reg-thankyou-title">
            <?php esc_html_e( 'Thank You for Submitting This Form!', 'ss-registration' ); ?>
        </h2>

        <!-- Doctor name badge -->
        <p class="ss-reg-thankyou-brand">Dr. Dipti Panhalkar</p>

        <!-- Confirmation message -->
        <p class="ss-reg-thankyou-msg">
            <?php esc_html_e( 'Thank you for your interest in a session with Dr. Dipti Panhalkar. Your details have been received. Connect with us on WhatsApp for further information and next steps.', 'ss-registration' ); ?>
        </p>

        <!-- ── Two CTA Buttons ─────────────────── -->
        <div class="ss-reg-cta-group">

            <!-- CTA 1 ▸ Chat on WhatsApp (direct) -->
            <div class="ss-reg-cta-item">
                <a
                    href="<?php echo esc_url( SS_WHATSAPP_CHAT_URL ); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="ss-reg-cta-btn ss-reg-cta-btn--primary"
                    id="ss-reg-wa-chat-btn"
                >
                    <svg class="ss-reg-cta-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.886 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    <?php esc_html_e( 'Chat on WhatsApp', 'ss-registration' ); ?>
                </a>
            </div>

            <!-- CTA 2 ▸ Join Session Group -->
            <div class="ss-reg-cta-item">
                <a
                    href="<?php echo esc_url( SS_WHATSAPP_GROUP_URL ); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="ss-reg-cta-btn ss-reg-cta-btn--secondary"
                    id="ss-reg-wa-group-btn"
                >
                    <svg class="ss-reg-cta-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.886 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    <?php esc_html_e( 'Join the Session Group', 'ss-registration' ); ?>
                </a>
                <p class="ss-reg-cta-sub">
                    <?php esc_html_e( 'Join our WhatsApp group for session updates, guidance, and related information.', 'ss-registration' ); ?>
                </p>
            </div>

        </div><!-- /.ss-reg-cta-group -->

    </div><!-- /.ss-reg-thankyou -->

</div><!-- /.ss-reg-wrapper -->
