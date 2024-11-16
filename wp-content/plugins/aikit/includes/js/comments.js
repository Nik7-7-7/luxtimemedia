"use strict";

jQuery(function($) {
    $(".aikit-top-hidden-toggle").click(function (event) {
        event.preventDefault();
        $(".aikit-top-hidden-note").toggle(100);
    });

    $(".aikit-comments-job-delete").click(function (event) {
        event.preventDefault();

        if (confirm($(this).data('confirm-message'))) {
            deleteJob($(this).data('id'));
        }
    });

    const deleteJob = function (jobId) {
        // extract text using ajax request
        $.ajax({
            type: "POST",
            url: aikit.siteUrl + '/?rest_route=/aikit/comments/v1/delete',
            data: JSON.stringify({
                id: jobId,
            }),
            encode: true,
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': aikit.nonce,
            },
        }).success(function (response) {

            if (!response.success) {
                alert(response.message);
                return;
            }

            location.reload();

        }).error(function (response) {
            if (response.responseJSON.message) {
                alert(response.responseJSON.message);
                return;
            }

            alert('An error occurred. Please try again.');
        });
    }

    $("#aikit-comments-delete-all").click(function (event) {
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

});
