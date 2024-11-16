"use strict";

jQuery(function($) {

    $("#aikit-post-finder-search-button").click(function (event) {
        event.preventDefault();

        searchPosts($(this));
    });

    $("#aikit-post-finder-add-all").click(function (event) {
        event.preventDefault();
        // trigger the click event on all the add buttons
        $('.aikit-post-finder-insert-post').trigger('click');
    });

    // onclick handler for inserting a post
    $(document).on('click', '.aikit-post-finder-insert-post', function (event) {
        let postId = $(this).data('post-id');
        postId = postId.toString();

        let selectedPosts = $('#aikit-post-finder-selected-posts').val();

        let postIds = [];
        if (selectedPosts.length > 0) {
            postIds = selectedPosts.split(',');
        }
        if (postIds.includes(postId)) {
            return;
        }

        postIds.push(postId);

        $('#aikit-post-finder-selected-posts').val(postIds.join(','));

        // disable the add button
        $(this).prop('disabled', true);
        $(this).css('opacity', '0.5');

        let result = $(this).closest('.aikit-post-finder-result').clone();
        result.find('.aikit-post-finder-insert-post').remove();

        result.append('<a href="#" class="aikit-post-finder-remove-post" data-post-id="' + postId + '"><i class="bi bi-dash-circle-fill"></i></a>');

        $('#aikit-post-finder-selected-results').append(result);

    });

    // onclick handler for removing a post

    $(document).on('click', '.aikit-post-finder-remove-post', function (event) {
        event.preventDefault();

        let postId = $(this).data('post-id');
        postId = postId.toString();

        let selectedPosts = $('#aikit-post-finder-selected-posts').val();

        let postIds = selectedPosts.split(',');
        let index = postIds.indexOf(postId);
        if (index > -1) {
            postIds.splice(index, 1);
        }

        // restore the opacity of the add button
        $('.aikit-post-finder-insert-post[data-post-id="' + postId + '"]').prop('disabled', false).css('opacity', '1');

        $('#aikit-post-finder-selected-posts').val(postIds.join(','));

        $(this).closest('.aikit-post-finder-result').remove();
    });

    const searchPosts = function (button) {

        button.prepend('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        // disable the button
        button.prop('disabled', true);

        let formData = {
            search_term: $("#aikit-post-finder-search").val(),
            post_type: $("#aikit-post-finder-post-type").val(),
        };

        $.ajax({
            type: "POST",
            url: aikit.siteUrl + '/?rest_route=/aikit/post-finder/v1/find',
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

            // add the resulting posts to #aikit-post-finder-search-results using jQuery
            let results = response;
            let html = '';
            for (let i = 0; i < results.length; i++) {
                let title = results[i].title;

                if (title.length === 0) {
                    title = '(No title)';
                }

                if (title.length > 100) {
                    title = title.substring(0, 100) + '...';
                }

                html += '<div class="aikit-post-finder-result">';
                html += '<a href="' + results[i].url + '" target="_blank">' + title + '</a>';

                // add a button to insert the post
                html += '<a class="aikit-post-finder-insert-post" data-post-id="' + results[i].id + '"><i class="bi bi-plus-circle-fill"></i></a>';

                html += '</div>';
            }

            $('#aikit-post-finder-search-results').html(html);

        }).fail(function (response) {
            alert('Error: ' + response.responseText);
            button.find('.spinner-border').remove();
            button.prop('disabled', false);
        });
    }
});
