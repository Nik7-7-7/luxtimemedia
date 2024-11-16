"use strict";

jQuery(function($) {

    $("#aikit-chatbot-form").submit(function (event) {
        event.preventDefault();

        submitForm($(".aikit-chatbot-save-settings"));
    });

    $(".aikit-reset-colors").click(function (event) {
        event.preventDefault();

        $("#aikit-chatbot-appearance-main-color").val($('#aikit-chatbot-appearance-main-color').data('reset-value'));
        $("#aikit-chatbot-appearance-secondary-color").val($('#aikit-chatbot-appearance-secondary-color').data('reset-value'));
        $("#aikit-chatbot-appearance-title-color").val($('#aikit-chatbot-appearance-title-color').data('reset-value'));
        $("#aikit-chatbot-appearance-ai-message-bubble-color").val($('#aikit-chatbot-appearance-ai-message-bubble-color').data('reset-value'));
        $("#aikit-chatbot-appearance-ai-message-text-color").val($('#aikit-chatbot-appearance-ai-message-text-color').data('reset-value'));
        $("#aikit-chatbot-appearance-user-message-bubble-color").val($('#aikit-chatbot-appearance-user-message-bubble-color').data('reset-value'));
        $("#aikit-chatbot-appearance-user-message-text-color").val($('#aikit-chatbot-appearance-user-message-text-color').data('reset-value'));

        $("#aikit-chatbot-appearance-main-color").trigger('input');
        $("#aikit-chatbot-appearance-secondary-color").trigger('input');
        $("#aikit-chatbot-appearance-title-color").trigger('input');
        $("#aikit-chatbot-appearance-ai-message-bubble-color").trigger('input');
        $("#aikit-chatbot-appearance-ai-message-text-color").trigger('input');
        $("#aikit-chatbot-appearance-user-message-bubble-color").trigger('input');
        $("#aikit-chatbot-appearance-user-message-text-color").trigger('input');
    });

    // if embedding is enabled, show the .aikit-embedding-options
    $("input[name='aikit-chatbot-use-embeddings']").change(function () {
        if ($(this).val() === '1') {
            $(".aikit-embedding-options").slideDown('fast');
            $(".aikit-page-content-aware-container").slideUp('fast');
        } else {
            $(".aikit-embedding-options").slideUp('fast');
            $(".aikit-page-content-aware-container").slideDown('fast');
        }
    });

    // on load check if embedding is enabled
    if ($("input[name='aikit-chatbot-use-embeddings']:checked").val() === '1') {
        $(".aikit-embedding-options").slideDown('fast');
        $(".aikit-page-content-aware-container").slideUp('fast');
    } else {
        $(".aikit-embedding-options").slideUp('fast');
        $(".aikit-page-content-aware-container").slideDown('fast');
    }


    const submitForm = function (button) {

        if ($("input[name='aikit-chatbot-use-embeddings']:checked").val() === '1' && $("#aikit-chatbot-selected-embedding").val() === '') {
            alert($("#aikit-message-select-embedding").val());
            return;
        }

        button.prepend('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        // disable the button
        button.prop('disabled', true);

        let formData = {
            chatbot_model: $("#aikit-chatbot-model").val(),
            chatbot_log_messages: $("input[name='aikit-chatbot-log-messages']:checked").val(),
            chatbot_prompt_stop_sequence: $("#aikit-chatbot-prompt-stop-sequence").val(),
            chatbot_completion_stop_sequence: $("#aikit-chatbot-completion-stop-sequence").val(),
            chatbot_show_on: $("#aikit-chatbot-show-on").val(),
            chatbot_enabled: $("input[name='aikit-chatbot-enabled']:checked").val(),
            chatbot_voice_enabled: $("input[name='aikit-chatbot-voice-enabled']:checked").val(),
            chatbot_default_view: $("input[name='aikit-chatbot-default-view']:checked").val(),
            chatbot_context: $("#aikit-chatbot-context").val(),
            chatbot_is_page_content_aware: $("#aikit-chatbot-is-page-content-aware").is(':checked'),
            chatbot_max_response_tokens: $("#aikit-chatbot-max-response-tokens").val(),
            chatbot_show_only_for_roles: $("#aikit-chatbot-show-only-for-roles").val(),
            chatbot_appearance_title: $("#aikit-chatbot-appearance-title").val(),
            chatbot_appearance_input_placeholder: $("#aikit-chatbot-appearance-input-placeholder").val(),
            chatbot_appearance_start_message: $("#aikit-chatbot-appearance-start-message").val(),
            chatbot_appearance_main_color: $("#aikit-chatbot-appearance-main-color").val(),
            chatbot_appearance_secondary_color: $("#aikit-chatbot-appearance-secondary-color").val(),
            chatbot_appearance_title_color: $("#aikit-chatbot-appearance-title-color").val(),
            chatbot_appearance_ai_message_bubble_color: $("#aikit-chatbot-appearance-ai-message-bubble-color").val(),
            chatbot_appearance_ai_message_text_color: $("#aikit-chatbot-appearance-ai-message-text-color").val(),
            chatbot_appearance_user_message_bubble_color: $("#aikit-chatbot-appearance-user-message-bubble-color").val(),
            chatbot_appearance_user_message_text_color: $("#aikit-chatbot-appearance-user-message-text-color").val(),
            chatbot_use_embeddings: $("input[name='aikit-chatbot-use-embeddings']:checked").val(),
            chatbot_selected_embedding: $("#aikit-chatbot-selected-embedding").val(),
            chatbot_embeddings_answer_formulation_prompt: $("#aikit-chatbot-embeddings-answer-formulation-prompt").val(),
        };

        let url = $('#aikit-chatbot-form').attr('action');

        $.ajax({
            type: "POST",
            url: url,
            data: JSON.stringify(formData),
            encode: true,
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': aikit.nonce,
            },
        }).success(function (response) {
            button.find('.spinner-border').remove();
            button.prop('disabled', false);

            // refresh the page
            location.reload();

        }).fail(function (response) {
            alert('Error: ' + response.responseText);
            button.find('.spinner-border').remove();
            button.prop('disabled', false);
        });
    }
});
