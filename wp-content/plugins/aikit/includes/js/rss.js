"use strict";

jQuery(function($) {

    $("#aikit-rss-form").submit(function (event) {
        event.preventDefault();

        submitForm($("#aikit-rss-add"));
    });

    $(".aikit-top-hidden-toggle").click(function (event) {
        event.preventDefault();
        $(".aikit-top-hidden-note").toggle(100);
    });

    $("#aikit-rss-url").blur(function () {
        let url = $(this).val();
        if (url === '') {
            return;
        }

        if (!isValidUrl(url)) {
            alert('Please enter a valid URL. (including http:// or https://)');
            return;
        }

        let spinner = $('<span class="spinner-border spinner-border-sm float-end m-2" role="status" aria-hidden="true"></span>');
        $(this).after(spinner);

        // extract text using ajax request
        $.ajax({
            type: "POST",
            url: aikit.siteUrl + '/?rest_route=/aikit/rss/v1/is-valid',
            data: JSON.stringify({
                url: url,
            }),
            dataType: "json",
            encode: true,
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': aikit.nonce,
            },
        }).success(function (response) {
            spinner.remove();
            if (!response.valid) {
                alert('Please enter a valid RSS feed URL.');
            }
        }).fail(function (response) {
            spinner.remove();
            alert('Error: ' + response.responseText);
        });
    });

    $(".aikit-rss-jobs-delete").on('click', function (event) {
        event.preventDefault();
        if (confirm($(this).data('confirm-message'))) {
            let deleteUrl = $(this).attr('href');
            $.ajax({
                type: "POST",
                url: deleteUrl,
                data: JSON.stringify({
                    id: $(this).data('id'),
                }),
                dataType: "json",
                encode: true,
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': aikit.nonce,
                },
            }).success(function (response) {
                // refresh the page
                location.reload();
            }).fail(function (response) {
                alert('Error: ' + response.responseText);
            });
        }
    });

    const submitForm = function (button) {
        if (!isProperlyConfigured()) {
            alert('Please enter a valid OpenAI API key in the settings page.');
            return;
        }

        if ($("#aikit-rss-url").val() === '') {
            alert($('#aikit-rss-url').data('validation-message'));
            return;
        }

        button.prepend('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        // disable the button
        button.prop('disabled', true);

        let prompts = {};
        $(".aikit-rss-prompt").each(function () {
            prompts[$(this).data('prompt-id')] = $(this).val();
        });

        let formData = {
            url: $("#aikit-rss-url").val(),
            include_featured_image: $("#aikit-rss-include-featured-image").is(':checked'),
            number_of_articles: $("#aikit-rss-articles").val(),
            post_type: $("#aikit-rss-post-type").val(),
            post_status: $("#aikit-rss-post-status").val(),
            post_category: $("#aikit-rss-post-category").val(),
            refresh_interval: $("#aikit-rss-schedule-interval").val(),
            generation_time_padding: $("#aikit-rss-generation-time-padding").val(),
            prompts: prompts,
            model: $("#aikit-rss-model").val(),
            save_prompts: $("#aikit-rss-save-prompts").is(':checked'),
        };

        let isEdit = false;
        // if there is a job id, add it to the form data
        if ($("#aikit-rss-job-id").val() !== '') {
            isEdit = true;
            formData.id = $("#aikit-rss-job-id").val();
            formData.is_active = $("#aikit-rss-status").val();
        }

        let url = $('#aikit-rss-form').attr('action');

        $.ajax({
            type: "POST",
            url: url,
            data: JSON.stringify(formData),
            dataType: "json",
            encode: true,
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': aikit.nonce,
            },
        }).success(function (response) {
            button.find('.spinner-border').remove();
            button.prop('disabled', false);

            if (isEdit) {
                showToast(
                    translate('Updated'),
                    translate('RSS job') +
                    ' <a href="' + response.url + '">' +
                    translate('updated Successfully.') +
                    '</a>');
                return;
            }

            showToast(
                translate('Created'),
                translate('RSS job') +
                ' <a href="' + response.url + '">' +
                translate('created Successfully.') +
                '</a>');

        }).fail(function (response) {
            alert('Error: ' + response.responseText);
            button.find('.spinner-border').remove();
            button.prop('disabled', false);
        });
    }

    $("#aikit-rss-reset-prompts").on('click', function (e) {
        e.preventDefault();
        if (confirm($(this).data('confirm-message'))) {
            let url = $(this).attr('href');
            $.ajax({
                type: "POST",
                url: url,
                data: JSON.stringify({}),
                dataType: "json",
                encode: true,
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': aikit.nonce,
                },
            }).success(function (response) {
                // refresh the page
                location.reload();
            }).fail(function (response) {
                alert('Error: ' + response.responseText);
            });
        }
    });

    const translate = function (text) {
        let translations = JSON.parse($('#aikit-rss-translations').val());
        if (translations[text]) {
            return translations[text];
        }

        return text;
    }

    const showToast = function (title, message) {

        let container = $('<div class="toast-container position-fixed bottom-0 end-0 p-3">');
        let toast = $('<div class="toast" role="alert" aria-live="assertive" aria-atomic="true">');
        let toastHeader = $('<div class="toast-header">');
        toastHeader.append('<strong class="me-auto">' + title + '</strong>');
        toastHeader.append('<button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>');
        let toastBody = $('<div class="toast-body">');

        toastBody.append(message);
        toast.append(toastHeader);
        toast.append(toastBody);
        container.append(toast);
        $('body').append(container);

        // show the toast
        let toastEl = new bootstrap.Toast(toast);
        toastEl.show();
    }

    const isProperlyConfigured = function () {
        if (aikit.isOpenAIKeyValid === undefined || aikit.isOpenAIKeyValid === "0" || aikit.isOpenAIKeyValid === "" || aikit.isOpenAIKeyValid === false) {
            return false;
        }

        return true;
    }

    const isValidUrl = function (url) {
        try {
            new URL(url);
        } catch (_) {
            return false;
        }

        return true;
    }
});
