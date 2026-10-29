/**
 * Dr. Dipti Panhalkar Session Registration Form — Frontend JS
 * Handles AJAX submission, validation, loading state, and thank-you reveal.
 */
( function ( $ ) {
    'use strict';

    // ---------------------------------------------------------------------------
    // DOM refs
    // ---------------------------------------------------------------------------
    var $wrapper     = $( '#ss-reg-wrapper' );
    var $formPanel   = $( '#ss-reg-form-panel' );
    var $form        = $( '#ss-reg-form' );
    var $notice      = $( '#ss-reg-notice' );
    var $submitBtn   = $( '#ss-reg-submit' );
    var $submitText  = $submitBtn.find( '.ss-reg-submit-text' );
    var $spinner     = $submitBtn.find( '.ss-reg-spinner' );
    var $thankyou    = $( '#ss-reg-thankyou-panel' );

    // Duplicate-submission guard
    var isSubmitting = false;

    // ---------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------

    function showNotice( message, type ) {
        $notice
            .removeClass( 'ss-reg-notice--error ss-reg-notice--success' )
            .addClass( 'ss-reg-notice--' + type )
            .text( message )
            .removeAttr( 'hidden' );

        // Scroll notice into view smoothly
        if ( $notice[0] ) {
            $notice[0].scrollIntoView( { behavior: 'smooth', block: 'nearest' } );
        }
    }

    function hideNotice() {
        $notice.attr( 'hidden', '' ).text( '' );
    }

    function setFieldError( fieldId, message ) {
        var $input = $( '#ss-' + fieldId );
        var $error = $( '#ss-' + fieldId + '-error' );
        $input.addClass( 'ss-reg-input--error' ).attr( 'aria-invalid', 'true' );
        $error.text( message );
    }

    function clearFieldError( fieldId ) {
        var $input = $( '#ss-' + fieldId );
        var $error = $( '#ss-' + fieldId + '-error' );
        $input.removeClass( 'ss-reg-input--error' ).removeAttr( 'aria-invalid' );
        $error.text( '' );
    }

    function clearAllErrors() {
        var fields = [ 'full-name', 'phone', 'profession', 'location' ];
        fields.forEach( function ( f ) { clearFieldError( f ); } );
        $( '#ss-seriousness-error' ).text( '' );
    }

    function setLoading( loading ) {
        if ( loading ) {
            $submitBtn.prop( 'disabled', true ).attr( 'aria-busy', 'true' );
            $submitText.text( ssRegData.submitting );
            $spinner.removeAttr( 'hidden' );
        } else {
            $submitBtn.prop( 'disabled', false ).removeAttr( 'aria-busy' );
            $submitText.text( ssRegData.submitText );
            $spinner.attr( 'hidden', '' );
        }
    }

    function showThankYou() {
        $formPanel.attr( 'hidden', '' );
        $thankyou.removeAttr( 'hidden' );
        $thankyou[0].scrollIntoView( { behavior: 'smooth', block: 'start' } );

        // Fire GA4 / GTM conversion event (only after confirmed server success).
        if ( typeof window.gtag === 'function' ) {
            window.gtag( 'event', 'ss_registration_complete', {
                event_category: 'Registration',
                event_label:    'Dr. Dipti Panhalkar',
            } );
        }
        if ( typeof window.dataLayer !== 'undefined' ) {
            window.dataLayer.push( {
                event:          'ss_registration_complete',
                event_category: 'Registration',
                event_label:    'Dr. Dipti Panhalkar',
            } );
        }
    }

    // ---------------------------------------------------------------------------
    // Client-side validation
    // ---------------------------------------------------------------------------

    function validateForm( data ) {
        var valid = true;

        // Full Name
        if ( ! data.full_name.trim() ) {
            setFieldError( 'full-name', 'Full Name is required.' );
            valid = false;
        } else {
            clearFieldError( 'full-name' );
        }

        // Phone — allow international formats
        var phoneRegex = /^\+?[0-9\s\-\(\)]{7,20}$/;
        if ( ! data.phone.trim() || ! phoneRegex.test( data.phone.trim() ) ) {
            setFieldError( 'phone', 'Enter a valid Phone Number.' );
            valid = false;
        } else {
            clearFieldError( 'phone' );
        }

        // Profession
        var allowed = [
            'Business Owner', 'Working Professional', 'Trainer or Coach',
            'Student', 'Entrepreneur', 'Freelancer', 'Other'
        ];
        if ( ! data.profession || allowed.indexOf( data.profession ) === -1 ) {
            setFieldError( 'profession', 'Please select your Profession.' );
            valid = false;
        } else {
            clearFieldError( 'profession' );
        }

        // Location
        if ( ! data.location.trim() ) {
            setFieldError( 'location', 'Your Location is required.' );
            valid = false;
        } else {
            clearFieldError( 'location' );
        }

        // Seriousness
        if ( ! data.seriousness ) {
            $( '#ss-seriousness-error' ).text( 'Please select an option.' );
            valid = false;
        } else {
            $( '#ss-seriousness-error' ).text( '' );
        }

        return valid;
    }

    // ---------------------------------------------------------------------------
    // Live inline validation (blur events)
    // ---------------------------------------------------------------------------

    $( '#ss-full-name' ).on( 'blur', function () {
        if ( $( this ).val().trim() ) {
            clearFieldError( 'full-name' );
        }
    } );

    $( '#ss-phone' ).on( 'blur', function () {
        var phoneRegex = /^\+?[0-9\s\-\(\)]{7,20}$/;
        if ( phoneRegex.test( $( this ).val().trim() ) ) {
            clearFieldError( 'phone' );
        }
    } );

    $( '#ss-profession' ).on( 'change', function () {
        if ( $( this ).val() ) {
            clearFieldError( 'profession' );
        }
    } );

    $( '#ss-location' ).on( 'blur', function () {
        if ( $( this ).val().trim() ) {
            clearFieldError( 'location' );
        }
    } );

    $( 'input[name="seriousness"]' ).on( 'change', function () {
        $( '#ss-seriousness-error' ).text( '' );
    } );

    // ---------------------------------------------------------------------------
    // Form submission
    // ---------------------------------------------------------------------------

    $form.on( 'submit', function ( e ) {
        e.preventDefault();

        if ( isSubmitting ) {
            return;
        }

        hideNotice();
        clearAllErrors();

        var formData = {
            full_name:   $( '#ss-full-name' ).val(),
            phone:       $( '#ss-phone' ).val(),
            profession:  $( '#ss-profession' ).val(),
            location:    $( '#ss-location' ).val(),
            seriousness: $( 'input[name="seriousness"]:checked' ).val() || '',
        };

        // Client-side validation first
        if ( ! validateForm( formData ) ) {
            showNotice( 'Please fill in all required fields correctly.', 'error' );
            // Focus first invalid field
            $form.find( '.ss-reg-input--error' ).first().trigger( 'focus' );
            return;
        }

        isSubmitting = true;
        setLoading( true );

        $.ajax( {
            type:    'POST',
            url:     ssRegData.ajaxUrl,
            data:    $.extend( {}, formData, {
                action:   'ss_registration_submit',
                ss_nonce: $( '#ss_nonce' ).val(),
            } ),
            timeout: 30000,

            success: function ( response ) {
                if ( response.success ) {
                    showThankYou();
                } else {
                    var msg = ( response.data && response.data.message )
                        ? response.data.message
                        : ssRegData.errorMsg;
                    showNotice( msg, 'error' );
                    isSubmitting = false;
                    setLoading( false );
                }
            },

            error: function ( xhr, status ) {
                var msg = ssRegData.errorMsg;
                if ( status === 'timeout' ) {
                    msg = 'The request timed out. Please check your connection and try again.';
                }
                showNotice( msg, 'error' );
                isSubmitting = false;
                setLoading( false );
            },
        } );
    } );

} )( jQuery );

