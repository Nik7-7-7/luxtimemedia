"use strict";

jQuery(function($) {

    // on body when aikit-auto-writer-nav is clicked
    $("body").on("click", ".aikit-auto-writer-nav a", function (event) {
        event.preventDefault();
        // parse the href parameters and get the paged value
        let url = new URL($(this).attr('href'));
        let paged = url.searchParams.get("paged");

        loadPostPage(paged);
    });

    // if value of radio with name=aikit-auto-writer-type changes, if it's multiple-topics, show .aikit-autowriter-strategy
    $("input[name='aikit-auto-writer-type']").change(function () {
        if ($(this).val() === 'multiple-topics') {
            // with animation
            $(".aikit-autowriter-strategy").show(100);
        } else {
            $(".aikit-autowriter-strategy").hide(100);
        }
    });

    // trigger change on load
    $("input[name='aikit-auto-writer-type']:checked").trigger('change');

    $(".aikit-top-hidden-toggle").click(function (event) {
        event.preventDefault();
        $(".aikit-top-hidden-note").toggle(100);
    });

    $('#aikit-auto-writer-schedule').on('click', function () {
        toggleScheduleOptions();
        return false;
    });

    $('#aikit-auto-writer-cancel-schedule').on('click', function () {
        toggleScheduleOptions();
        return false;
    });

    $('#aikit-auto-writer-confirm-schedule').on('click', function () {
        let valid = $('#aikit-auto-writer-form').get(0).reportValidity();

        if (valid) {
            submitForm($(this), true);
        }
    });

    $("#aikit-auto-writer-form").submit(function (event) {
        event.preventDefault();

        submitForm($("#aikit-auto-writer-generate"), false);
    });

    $("#aikit-auto-writer-save").on('click', function () {
        submitForm($(this), true, true);
    });



    $(".aikit-scheduler-generators-delete").on('click', function (event) {
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

    $("#aikit-auto-writer-deactivate-all").click(function (event) {
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

    $("#aikit-auto-writer-delete-all").click(function (event) {
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

    $("#aikit-auto-writer-activate-all").click(function (event) {
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

    $("#aikit-auto-writer-reset-prompts").on('click', function (e) {
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

    const submitForm = function (button, isScheduled = false, isEdit = false) {
        if (!isProperlyConfigured()) {
            alert('Please enter a valid OpenAI API key in the settings page.');
            return;
        }

        if ($("#aikit-auto-writer-topic").val() === '') {
            alert($('#aikit-auto-writer-topic').data('validation-message'));
            return;
        }

        // add spinner to the button #aikit-auto-writer-generate bootstrap spinner
        button.prepend('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        // disable the button
        button.prop('disabled', true);

        let prompts = {};
        $(".aikit-auto-writer-prompt").each(function () {
            prompts[$(this).data('prompt-id')] = $(this).val();
        });

        let formData = {
            type: $("input[name='aikit-auto-writer-type']:checked").val(),
            strategy: $("input[name='aikit-auto-writer-strategy']:checked").val(),
            topic: $("#aikit-auto-writer-topic").val(),
            include_outline: $("#aikit-auto-writer-include-outline").is(':checked'),
            include_featured_image: $("#aikit-auto-writer-include-featured-image").is(':checked'),
            include_section_images: $("#aikit-auto-writer-include-section-images").is(':checked'),
            include_tldr: $("#aikit-auto-writer-include-tldr").is(':checked'),
            include_conclusion: $("#aikit-auto-writer-include-conclusion").is(':checked'),
            post_type: $("#aikit-auto-writer-post-type").val(),
            post_status: $("#aikit-auto-writer-post-status").val(),
            post_category: $("#aikit-auto-writer-post-category").val(),
            number_of_sections: $("#aikit-auto-writer-sections").val(),
            section_max_length: $("#aikit-auto-writer-words-per-section").val(),
            number_of_articles: $("#aikit-auto-writer-articles").val(),
            seo_keywords: $("#aikit-auto-writer-seo-keywords").val(),
            prompts: prompts,
            model: $("#aikit-auto-writer-model").val(),
            save_prompts: $("#aikit-auto-writer-save-prompts").is(':checked'),
        };

        if (isScheduled) {
            formData.interval = $("#aikit-auto-writer-schedule-interval").val();
            formData.max_runs = $("#aikit-auto-writer-max-runs").val();
        } else {
            formData.max_runs = 1;
            formData.interval = 'once';
        }

        if (isEdit) {
            formData.id = $("#aikit-auto-writer-generator-id").val();
            formData.active = $("#aikit-auto-writer-generator-status").val() === "1";
            formData.max_runs = $("#aikit-auto-writer-max-runs").val();
        }

        let url = $('#aikit-auto-writer-form').attr('action');
        if (isScheduled) {
            url = url + '&scheduled=1';
        }

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

            if (!isEdit) {
                showToast(
                    '<i class="bi bi-check-circle-fill"></i> ' + translate('Scheduled'),
                    translate('AI Auto Writer') +
                    ' <a href="' + response.url + '">' +
                    translate('scheduled Successfully.') +
                    '</a>');
            }

        }).fail(function (response) {
            alert('Error: ' + response.responseText);
            button.find('.spinner-border').remove();
            button.prop('disabled', false);
        });
    }

    const translate = function (text) {
        // get translations from the #aikit-auto-writer-translations input
        let translations = JSON.parse($('#aikit-auto-writer-translations').val());
        if (translations[text]) {
            return translations[text];
        }

        return text;
    }

    const showToast = function (title, message) {

        let container = $('<div class="toast-container position-fixed bottom-0 end-0 p-3">');
        let toast = $('<div class="toast text-bg-primary" role="alert" aria-live="assertive" aria-atomic="true">');
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
        let toastEl = new bootstrap.Toast(toast, { delay: 7000 });
        toastEl.show();
    }

    const refreshPosts = function (number_of_articles) {
        loadPostPage(1, function () {
            $("#aikit-auto-writer-posts tbody tr").slice(0, number_of_articles).css('background-color', '#eff4fb');
            $("#aikit-auto-writer-posts tbody tr").slice(0, number_of_articles).animate({backgroundColor: '#fff'}, 3000);
        });
    }

    const toggleScheduleOptions = function () {
        $("#aikit-auto-writer-generate").toggle('fast');

        $(".row.aikit-schedule-options").toggle(100);

        $('#aikit-auto-writer-schedule').toggle(100);
    }

    const loadPostPage = function (paged, onSuccess) {
        $.ajax({
            type: "GET",
            url: aikit.siteUrl + '/?rest_route=/aikit/auto-writer/v1/list&paged=' + paged,
            dataType: "json",
            encode: true,
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': aikit.nonce,
            },
        }).success(function (response) {
            // replace with new posts
            $("#aikit-auto-writer-posts").html(response.body);

            if (typeof onSuccess === 'function') {
                onSuccess();
            }

        }).fail(function (response) {
            alert('Error: ' + response.responseText);
        });
    }

    const isProperlyConfigured = function () {
        if (aikit.isOpenAIKeyValid === undefined || aikit.isOpenAIKeyValid === "0" || aikit.isOpenAIKeyValid === "" || aikit.isOpenAIKeyValid === false) {
            return false;
        }

        return true;
    }
});
