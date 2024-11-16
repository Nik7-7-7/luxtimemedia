"use strict";

// on document load
document.addEventListener('DOMContentLoaded', function() {
    // max token multiplier option
    var slider = document.getElementById("aikit_setting_openai_max_tokens_multiplier");
    var output = document.getElementById("aikit_setting_openai_max_tokens_multiplier_value");

    slider.oninput = function() {
        var multiplier = this.value;
        multiplier = multiplier / 10;
        output.innerHTML = (1 + multiplier) + 'x';
    }

    slider.oninput();
});

// on window load jquery
jQuery(window).on('load', function() {
    // get the active_tab from the url
    let url = new URL(window.location.href);
    let active_tab = url.searchParams.get("tab");

    // if active_tab is set, then switch to that tab
    if (active_tab) {
        jQuery('#' + active_tab + '-tab').click();
    }

    // when a tab is clicked, update the url
    jQuery('#aikit-settings-tabs .nav-link').click(function() {
        let url = new URL(window.location.href);
        url.searchParams.set('tab', jQuery(this).attr('id').replace('-tab', ''));
        window.history.pushState({}, '', url);
    });

    jQuery('#aikit_setting_audio_player_primary_color').on('input', function() {
        let iframe = jQuery('.aikit-audio-player-iframe');

        let src = iframe.attr('src');
        let url = new URL(src);
        url.searchParams.set('aikit-primary-color', jQuery(this).val());
        iframe.attr('src', url.toString());
    });

    // do the same for #aikit_setting_audio_player_secondary_color
    jQuery('#aikit_setting_audio_player_secondary_color').on('input', function() {
        let iframe = jQuery('.aikit-audio-player-iframe');
        let src = iframe.attr('src');
        let url = new URL(src);
        url.searchParams.set('aikit-secondary-color', jQuery(this).val());
        iframe.attr('src', url.toString());
    });

    // aikit_setting_audio_player_message
    jQuery('#aikit_setting_audio_player_message').on('input',function() {
        jQuery('.aikit-audio-player-message').html(jQuery(this).val());
    });
});

window.onpopstate = function(event) {
    if(event && event.state) {
        location.reload();
    }
}