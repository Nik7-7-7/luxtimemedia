"use strict";

jQuery(function($) {

    $("#aikit-repurposer-form").submit(function (event) {
        event.preventDefault();

        submitForm($("#aikit-repurposer-generate"));
    });

    $(".aikit-top-hidden-toggle").click(function (event) {
        event.preventDefault();
        $(".aikit-top-hidden-note").toggle(100);
    });

    // on defocus of aikit-repurposer-url, check if it is a valid URL
    $("#aikit-repurposer-url").blur(function () {
        let url = $(this).val();
        if (url === '') {
            return;
        }

        if (!isValidUrl(url)) {
            alert('Please enter a valid URL. (including http:// or https://)');
            return;
        }

        // of the url equals the data-url attribute, then we have already extracted the text
        if (url === $(this).data('url')) {
            return;
        }

        // if the url is a youtube video, change the type to youtube
        if (isYoutubeVideo(url)) {
            $("input[name='aikit-repurposer-job-type'][value='youtube']").prop('checked', true);
        }

        //store the url as a data attribute
        $(this).data('url', url);

        // append a spinner to the text box
        let spinner = $('<span class="spinner-border spinner-border-sm float-end m-2" role="status" aria-hidden="true"></span>');
        $(this).after(spinner);

        // extract text using ajax request
        $.ajax({
            type: "POST",
            url: aikit.siteUrl + '/?rest_route=/aikit/repurposer/v1/extract-text',
            data: JSON.stringify({
                url: url,
                job_type: $("input[name='aikit-repurposer-job-type']:checked").val(),
            }),
            dataType: "json",
            encode: true,
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': aikit.nonce,
            },
        }).success(function (response) {
            spinner.remove();
            $("#aikit-repurposer-extracted-text").val(response.content);
            $("#aikit-repurposer-extracted-title").val(response.title);
            $(".aikit-repurposer-extracted-text-container").show(100);
        }).fail(function (response) {
            spinner.remove();
            alert('Error: ' + response.responseText);
        });
    });

    $('.aikit-repurposer-how-to').click(function (event) {
        event.preventDefault();
        $('.aikit-repurposer-how-to-content').toggle(100);
    });

    $("#aikit-repurposer-activate-all").click(function (event) {
        event.preventDefault();
        if (confirm($(this).data('confirm-message'))) {
            let deleteUrl = $(this).attr('href');
            $.ajax({
                type: "POST",
                url: deleteUrl,
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

    $(".aikit-repurposer-job-activate").on('click', function (event) {
        event.preventDefault();
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
    });

    $(".aikit-repurposer-job-deactivate").on('click', function (event) {
        event.preventDefault();
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
    });

    $("#aikit-repurposer-deactivate-all").click(function (event) {
        event.preventDefault();
        if (confirm($(this).data('confirm-message'))) {
            let deleteUrl = $(this).attr('href');
            $.ajax({
                type: "POST",
                url: deleteUrl,
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

    $("#aikit-repurposer-delete-all").click(function (event) {
        event.preventDefault();
        if (confirm($(this).data('confirm-message'))) {
            let deleteUrl = $(this).attr('href');
            $.ajax({
                type: "POST",
                url: deleteUrl,
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

    $(".aikit-repurposer-jobs-delete").on('click', function (event) {
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

        if ($("#aikit-repurposer-url").val() === '') {
            alert($('#aikit-repurposer-url').data('validation-message'));
            return;
        }

        button.prepend('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        // disable the button
        button.prop('disabled', true);

        let prompts = {};
        $(".aikit-repurposer-prompt").each(function () {
            prompts[$(this).data('prompt-id')] = $(this).val();
        });

        let formData = {
            job_type: $("input[name='aikit-repurposer-job-type']:checked").val(),
            url: $("#aikit-repurposer-url").val(),
            keywords: $("#aikit-repurposer-seo-keywords").val(),
            include_featured_image: $("#aikit-repurposer-include-featured-image").is(':checked'),
            number_of_articles: $("#aikit-repurposer-articles").val(),
            post_type: $("#aikit-repurposer-post-type").val(),
            post_status: $("#aikit-repurposer-post-status").val(),
            post_category: $("#aikit-repurposer-post-category").val(),
            prompts: prompts,
            model: $("#aikit-repurposer-model").val(),
            save_prompts: $("#aikit-repurposer-save-prompts").is(':checked'),
        };

        // if aikit-repurposer-extracted-text has text, then send it as well
        if ($("#aikit-repurposer-extracted-text").val() !== '') {
            formData.extracted_text = $("#aikit-repurposer-extracted-text").val();
        }

        // if aikit-repurposer-extracted-title has text, then send it as well
        if ($("#aikit-repurposer-extracted-title").val() !== '') {
            formData.extracted_title = $("#aikit-repurposer-extracted-title").val();
        }

        let url = $('#aikit-repurposer-form').attr('action');

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

            showToast(
                translate('Created'),
                translate('Repurpose job') +
                ' <a href="' + response.url + '">' +
                translate('created Successfully.') +
                '</a>');

        }).fail(function (response) {
            alert('Error: ' + response.responseText);
            button.find('.spinner-border').remove();
            button.prop('disabled', false);
        });
    }

    $("#aikit-repurposer-reset-prompts").on('click', function (e) {
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
        let translations = JSON.parse($('#aikit-repurposer-translations').val());
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

    const isYoutubeVideo = function (url) {
        return url.match(/^(https?\:\/\/)?(www\.)?(youtube\.com|youtu\.?be)\/.+$/);
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
