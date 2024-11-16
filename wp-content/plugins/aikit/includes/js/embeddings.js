"use strict";

jQuery(function($) {

    $(".aikit-embeddings-job-delete").click(function (event) {
        event.preventDefault();

        if (confirm($(this).data('confirm-message'))) {
            deleteJob($(this).data('id'));
        }
    });

    $(".aikit-top-hidden-toggle").click(function (event) {
        event.preventDefault();
        $(".aikit-top-hidden-note").toggle(100);
    });

    $(".aikit-count-tokens").click(function (event) {
        event.preventDefault();

        countTokens($(this));
    });

    $('input[name="aikit-embeddings-type"]').change(function (event) {
        if ($('input[name="aikit-embeddings-type"]:checked').val() === 'qdrant' && $('#aikit-qdrant-credentials-set').val() === '0') {
            $('.aikit-qdrant-credentials-warning').fadeIn();
            $('#aikit-embeddings-next-step-add-data').prop('disabled', true);
        } else {
            $('.aikit-qdrant-credentials-warning').hide();
            $('#aikit-embeddings-next-step-add-data').prop('disabled', false);
        }
    });

    $('input[name="aikit-embeddings-type"]').trigger('change');

    $('.aikit-export-as-csv').click(function (event) {
        let data = [];
        let enteredDataContainer = $('.aikit-entered-data-container');
        enteredDataContainer.each(function (index, element) {
            let enteredData = $(element).find('.aikit-entered-data').val();

            if (enteredData === '') {
                return;
            }

            data.push(
                enteredData,
            );
        });

        downloadCSV(data, 'data.csv');
    });

    $(document).on('click', '.aikit-remove-row', function (event) {
        event.preventDefault();

        if ($('.aikit-entered-data-container').length === 1) {
            return;
        }

        $(this).closest('.aikit-entered-data-container').remove();
    });

    $(".aikit-embeddings-approve-and-create-embeddings-button").click(function (event) {
        event.preventDefault();

        approveAndStartCreation($(this));
    });

    $('.aikit-embeddings-data-file-upload-button').click(function (event) {
        event.preventDefault();

        // toggle with animation

        $('#aikit-embeddings-data-file').toggle('fast');
    });

    $("#aikit-embeddings-data-file").change(function (event) {
        handleFileUpload(event);
    });

    $('.aikit-create-embeddings-button').click(function (event) {
        event.preventDefault();

        saveTrainingData($(this), true);
    });

    $('.aikit-preprocess-data-button').click(function (event) {
        event.preventDefault();

        saveTrainingData($(this), true);
    });

    $('.aikit-save-data-button').click(function (event) {
        event.preventDefault();

        saveTrainingData($(this));
    });

    // depending on the value of radio with name= aikit-embeddings-training-data-source show the appropriate div
    let trainingDataSource = $('input[name="aikit-embeddings-data-source"]:checked').val();
    if (trainingDataSource === 'manual') {
        $('.aikit-embeddings-data-manual').fadeIn();
        $('.aikit-embeddings-data-posts').hide();
        $('.aikit-create-embeddings-button').fadeIn();
        $('.aikit-preprocess-data-button').hide();
        $('.aikit-token-count-row').fadeIn();
        $('.aikit-token-count-description').fadeIn();
    } else if (trainingDataSource === 'posts') {
        $('.aikit-embeddings-data-manual').hide();
        $('.aikit-embeddings-data-posts').fadeIn();
        $('.aikit-create-embeddings-button').hide();
        $('.aikit-preprocess-data-button').fadeIn();
        $('.aikit-token-count-row').hide();
        $('.aikit-token-count-description').hide();
    }

    $('#aikit-embeddings-data-source-manual').click(function (event) {
        $('.aikit-embeddings-data-manual').fadeIn();
        $('.aikit-embeddings-data-posts').hide();
        $('.aikit-create-embeddings-button').fadeIn();
        $('.aikit-preprocess-data-button').hide();
        $('.aikit-token-count-row').fadeIn();
        $('.aikit-token-count-description').fadeIn();
    });

    $('#aikit-embeddings-data-source-posts').click(function (event) {

        $('.aikit-embeddings-data-manual').hide();
        $('.aikit-embeddings-data-posts').fadeIn();
        $('.aikit-create-embeddings-button').hide();
        $('.aikit-preprocess-data-button').fadeIn();
        $('.aikit-token-count-row').hide();
        $('.aikit-token-count-description').hide();
    });

    $(document).on('blur', '.aikit-entered-data', function (event) {
        let lastPrompt = $('.aikit-entered-data').last();
        if (lastPrompt.val() !== '') {
            addDataRow();
        }
    });

    $("#aikit-embeddings-form-first-step").submit(function (event) {
        event.preventDefault();

        firstStep($(this));
    });

    const deleteJob = function (jobId) {
        // extract text using ajax request
        $.ajax({
            type: "POST",
            url: aikit.siteUrl + '/?rest_route=/aikit/embeddings/v1/delete',
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

    const countTokens = function (button) {
        let data = [];
        let enteredDataContainer = $('.aikit-entered-data-container');
        enteredDataContainer.each(function (index, element) {
            let enteredData = $(element).find('.aikit-entered-data').val();

            if (enteredData === '') {
                return;
            }

            data.push(
                enteredData,
            );
        });

        // append a spinner to the text box
        let spinner = $('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>');
        button.prepend(spinner);
        // disable the button
        button.prop('disabled', true);

        // extract text using ajax request
        $.ajax({
            type: "POST",
            url: aikit.siteUrl + '/?rest_route=/aikit/embeddings/v1/count-tokens',
            data: JSON.stringify({
                rows: data,
            }),
            encode: true,
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': aikit.nonce,
            },
        }).success(function (response) {
            $('.aikit-token-count').text(response.count);

            spinner.remove();
            button.prop('disabled', false);

        }).error(function (response) {
            spinner.remove();
            button.prop('disabled', false);

            alert('An error occurred. Please try again.');
        });
    }

    const addDataRow = function (data = '') {
        let row = $('<div class="row mt-2 aikit-entered-data-container"></div>');
        let col = $('<div class="col"></div>');
        let textarea = $('<textarea class="form-control aikit-entered-data" rows="3"></textarea>');
        textarea.val(data);
        col.append(textarea);
        row.append(col);

        col = $('<div class="col-2"></div>');
        let button = $('<button class="btn btn-sm btn-outline-danger aikit-remove-row"><i class="bi bi-trash"></i></button>');
        col.append(button);
        row.append(col);

        $('.aikit-embeddings-data-manual-inputs').append(row);
    }

    const approveAndStartCreation = function (button) {

        let data = [];

        let enteredDataContainer = $('.aikit-entered-data-container');
        enteredDataContainer.each(function (index, element) {
            let enteredData = $(element).find('.aikit-entered-data').val();

            if (enteredData === '') {
                return;
            }

            data.push(
                enteredData,
            );
        });

        if (data.length === 0) {
            alert('Please enter at least one prompt/completion pair');
            return;
        }

        button.prepend('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        // disable the button
        button.prop('disabled', true);

        let formData = {
            entered_data: data,
            job_id: $("#aikit-embeddings-job-id").val(),
            next_step: true,
        }

        $.ajax({
            url: aikit.siteUrl + '/?rest_route=/aikit/embeddings/v1/edit',
            type: 'POST',
            data: JSON.stringify(formData),
            encode: true,
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': aikit.nonce,
            },
            success: function (response) {
                document.location.href = response.redirect_url;

            },
            fail: function (response) {
                alert(response.responseText);
            },
            complete: function () {
                button.prop('disabled', false);
                button.find('.spinner-border').remove();
            }
        });
    }

    const saveTrainingData = function (button, next_step = false) {
        // get the radio button value with name aikit-embeddings-training-data-source
        let trainingDataSource = $('input[name="aikit-embeddings-data-source"]:checked').val();

        let data = [];

        let enteredDataContainer = $('.aikit-entered-data-container');
        enteredDataContainer.each(function (index, element) {
            let enteredData = $(element).find('.aikit-entered-data').val();

            if (enteredData === '') {
                return;
            }

            data.push(
                enteredData,
            );
        });

        if (next_step && data.length === 0 && trainingDataSource === "manual") {
            alert('Please enter at least one prompt/completion pair.');
            return;
        }

        // get the post data #aikit-post-finder-selected-posts hidden input
        let postData = [];
        let selectedPosts = $('#aikit-post-finder-selected-posts').val();
        if (selectedPosts !== '') {
            postData = selectedPosts.split(',');
        }

        if (next_step && trainingDataSource === "posts") {
            if (postData.length === 0) {
                alert('Please select at least one post.');
                return;
            }
        }

        button.prepend('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        // disable the button
        button.prop('disabled', true);

        let formData = {
            data_source: trainingDataSource,
            entered_data: data,
            chosen_post_ids: postData,
            job_id: $("#aikit-embeddings-job-id").val(),
        }

        if (next_step) {
            formData.next_step = true;
        }

        $.ajax({
            url: aikit.siteUrl + '/?rest_route=/aikit/embeddings/v1/edit',
            type: 'POST',
            data: JSON.stringify(formData),
            encode: true,
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': aikit.nonce,
            },
            success: function (response) {
                document.location.href = response.redirect_url;
            },
            fail: function (response) {
                alert(response.responseText);
            },
            complete: function () {
                button.prop('disabled', false);
                button.find('.spinner-border').remove();
            }
        });
    }

    const firstStep = function (button) {
        // validate the form
        let errors = [];

        // get the validation errors from data-validation-message attribute

        if ($("#aikit-embeddings-name").val() === '') {
            errors.push($("#aikit-embeddings-output-model-name").data('validation-message'));
        }

        if (errors.length > 0) {
            alert(errors.join("\n"));
            return;
        }


        button.prepend('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        // disable the button
        button.prop('disabled', true);

        let formData = {
            embeddings_name: $("#aikit-embeddings-name").val(),
            // radio button
            embeddings_type: $('input[name="aikit-embeddings-type"]:checked').val(),
        };

        $.ajax({
            type: "POST",
            url: aikit.siteUrl + '/?rest_route=/aikit/embeddings/v1/create',
            data: JSON.stringify(formData),
            encode: true,
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': aikit.nonce,
            },
        }).success(function (response) {
            button.find('.spinner-border').remove();
            button.prop('disabled', false);

            document.location.href = response.redirect_url;

        }).fail(function (response) {
            alert('Error: ' + response.responseText);
            button.find('.spinner-border').remove();
            button.prop('disabled', false);
        });
    }

    const handleFileUpload = function (event) {
        const file = event.target.files[0]; // Get the uploaded file

        // Create a file reader
        const reader = new FileReader();

        // Set up the reader's onload event handler
        reader.onload = function (e) {
            const csvData = e.target.result; // Get the CSV data

            // check if the file is csv
            if (file.type !== 'text/csv') {
                alert('Please upload a CSV file');
                return;
            }

            // Process the CSV data
            processCsvData(csvData);
        };

        // Read the uploaded file as text
        reader.readAsText(file);
    }

    const processCsvData = function (csvData) {
        let rows = CSVToArray(csvData);

        // Iterate over rows
        for (let i = 0; i < rows.length; i++) {
            let columns = rows[i];

            addDataRow(columns[0]);
        }
    }

    function convertToCSV(data) {
        let csv = "";

        // Add data rows
        data.forEach(function(item) {
            // add row and each column data show be enclosed in double quotes
            csv +=
                '"' +
                item.replace(/"/g, '""') +
                '"\n';
        });

        return csv;
    }

    function CSVToArray( strData, strDelimiter ){

        console.log(strData);
        // Check to see if the delimiter is defined. If not,
        // then default to comma.
        strDelimiter = (strDelimiter || ",");

        // Create a regular expression to parse the CSV values.
        let objPattern = new RegExp(
            (
                // Delimiters.
                "(\\" + strDelimiter + "|\\r?\\n|\\r|^)" +

                // Quoted fields.
                "(?:\"([^\"]*(?:\"\"[^\"]*)*)\"|" +

                // Standard fields.
                "([^\"\\" + strDelimiter + "\\r\\n]*))"
            ),
            "gi"
        );

        // Create an array to hold our data. Give the array
        // a default empty first row.
        let arrData = [[]];

        // Create an array to hold our individual pattern
        // matching groups.
        let arrMatches = null;

        // Keep looping over the regular expression matches
        // until we can no longer find a match.
        while (arrMatches = objPattern.exec( strData )){

            // Get the delimiter that was found.
            let strMatchedDelimiter = arrMatches[ 1 ];

            // Check to see if the given delimiter has a length
            // (is not the start of string) and if it matches
            // field delimiter. If id does not, then we know
            // that this delimiter is a row delimiter.
            if (
                strMatchedDelimiter.length &&
                (strMatchedDelimiter != strDelimiter)
            ){

                // Since we have reached a new row of data,
                // add an empty row to our data array.
                arrData.push( [] );
            }

            let strMatchedValue
            // Now that we have our delimiter out of the way,
            // let's check to see which kind of value we
            // captured (quoted or unquoted).
            if (arrMatches[ 2 ]){
                // We found a quoted value. When we capture
                // this value, unescape any double quotes.
                strMatchedValue = arrMatches[ 2 ].replace(
                    new RegExp( "\"\"", "g" ),
                    "\""
                );
            } else {
                // We found a non-quoted value.
                strMatchedValue = arrMatches[ 3 ];
            }

            // Now that we have our value string, let's add
            // it to the data array.
            arrData[ arrData.length - 1 ].push( strMatchedValue );
        }

        // Return the parsed data.
        return( arrData );
    }

    function downloadCSV(data, filename) {
        let csv = convertToCSV(data);
        let csvBlob = new Blob([csv], { type: "text/csv;charset=utf-8;" });

        if (navigator.msSaveBlob) { // For IE and Edge
            navigator.msSaveBlob(csvBlob, filename);
        } else {
            let link = document.createElement("a");
            if (link.download !== undefined) {
                // Create a link element, set its attributes
                link.setAttribute("href", URL.createObjectURL(csvBlob));
                link.setAttribute("download", filename);

                // Append the link to the body
                document.body.appendChild(link);

                // Trigger the download
                link.click();

                // Cleanup
                document.body.removeChild(link);
            }
        }
    }

});
