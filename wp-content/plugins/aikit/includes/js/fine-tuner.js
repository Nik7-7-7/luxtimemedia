"use strict";

jQuery(function($) {

    $(".aikit-fine-tune-job-delete").click(function (event) {
        event.preventDefault();

        if (confirm($(this).data('confirm-message'))) {
            deleteJob($(this).data('id'));
        }
    });

    $(".aikit-count-tokens").click(function (event) {
        event.preventDefault();

        countTokens($(this));
    });

    $('.aikit-export-as-csv').click(function (event) {
        let promptCompletionData = [];
        let promptCompletionPairs = $('.aikit-pair');
        promptCompletionPairs.each(function (index, element) {
            let prompt = $(element).find('.aikit-prompt').val();
            let completion = $(element).find('.aikit-completion').val();

            if (prompt === '' && completion === '') {
                return;
            }

            promptCompletionData.push({
                prompt: prompt,
                completion: completion,
            });
        });

        downloadCSV(promptCompletionData, 'prompt-completion.csv');
    });

    $(document).on('click', '.aikit-remove-pair', function (event) {
        event.preventDefault();

        if ($('.aikit-pair').length === 1) {
            return;
        }

        $(this).closest('.aikit-pair').remove();
    });

    $(".aikit-fine-tune-approve-and-start-fine-tuning-button").click(function (event) {
        event.preventDefault();

        approveAndStartFineTuning($(this));
    });

    $('.aikit-fine-tune-training-data-file-upload-button').click(function (event) {
        event.preventDefault();

        // toggle with animation

        $('#aikit-fine-tune-training-data-file').toggle('fast');
    });

    $("#aikit-fine-tune-training-data-file").change(function (event) {
        handleFileUpload(event);
    });

    $('.aikit-fine-tune-start-fine-tuning-button').click(function (event) {
        event.preventDefault();

        saveTrainingData($(this), true);
    });

    $('.aikit-fine-tune-preprocess-data-button').click(function (event) {
        event.preventDefault();

        saveTrainingData($(this), true);
    });

    $('.aikit-fine-tune-save-training-data-button').click(function (event) {
        event.preventDefault();

        saveTrainingData($(this));
    });

    // depending on the value of radio with name= aikit-fine-tune-training-data-source show the appropriate div
    let trainingDataSource = $('input[name="aikit-fine-tune-training-data-source"]:checked').val();
    if (trainingDataSource === 'manual') {
        $('.aikit-fine-tune-training-data-manual').fadeIn();
        $('.aikit-fine-tune-training-data-posts').hide();
        $('.aikit-fine-tune-start-fine-tuning-button').fadeIn();
        $('.aikit-fine-tune-preprocess-data-button').hide();
        $('.aikit-token-count-row').fadeIn();
        $('.aikit-token-count-description').fadeIn();
    } else if (trainingDataSource === 'posts') {
        $('.aikit-fine-tune-training-data-manual').hide();
        $('.aikit-fine-tune-training-data-posts').fadeIn();
        $('.aikit-fine-tune-start-fine-tuning-button').hide();
        $('.aikit-fine-tune-preprocess-data-button').fadeIn();
        $('.aikit-token-count-row').hide();
        $('.aikit-token-count-description').hide();
    }

    $('#aikit-fine-tune-training-data-source-manual').click(function (event) {
        $('.aikit-fine-tune-training-data-manual').fadeIn();
        $('.aikit-fine-tune-training-data-posts').hide();
        $('.aikit-fine-tune-start-fine-tuning-button').fadeIn();
        $('.aikit-fine-tune-preprocess-data-button').hide();
        $('.aikit-token-count-row').fadeIn();
        $('.aikit-token-count-description').fadeIn();
    });

    $('#aikit-fine-tune-training-data-source-posts').click(function (event) {

        $('.aikit-fine-tune-training-data-manual').hide();
        $('.aikit-fine-tune-training-data-posts').fadeIn();
        $('.aikit-fine-tune-start-fine-tuning-button').hide();
        $('.aikit-fine-tune-preprocess-data-button').fadeIn();
        $('.aikit-token-count-row').hide();
        $('.aikit-token-count-description').hide();
    });

    $(document).on('blur', '.aikit-prompt', function (event) {
        let lastPrompt = $('.aikit-prompt').last();
        if (lastPrompt.val() !== '') {
            addPromptCompletionRow();
        }
    });

    $(document).on('blur', '.aikit-completion', function (event) {
        let lastCompletion = $('.aikit-completion').last();
        if (lastCompletion.val() !== '') {
            addPromptCompletionRow();
        }
    });


    $("#aikit-fine-tune-form-first-step").submit(function (event) {
        event.preventDefault();

        firstStep($(this));
    });

    const deleteJob = function (jobId) {
        // extract text using ajax request
        $.ajax({
            type: "POST",
            url: aikit.siteUrl + '/?rest_route=/aikit/fine-tune/v1/delete',
            data: JSON.stringify({
                id: jobId,
            }),
            encode: true,
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': aikit.nonce,
            },
        }).success(function (response) {
            location.reload();

        }).error(function (response) {
            alert('An error occurred. Please try again.');
        });
    }

    const countTokens = function (button) {
        let promptCompletionData = [];
        let promptCompletionPairs = $('.aikit-pair');
        promptCompletionPairs.each(function (index, element) {
            let prompt = $(element).find('.aikit-prompt').val();
            let completion = $(element).find('.aikit-completion').val();

            if (prompt === '' && completion === '') {
                return;
            }

            promptCompletionData.push({
                prompt: prompt,
                completion: completion,
            });
        });

        // append a spinner to the text box
        let spinner = $('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>');
        button.prepend(spinner);
        // disable the button
        button.prop('disabled', true);

        // extract text using ajax request
        $.ajax({
            type: "POST",
            url: aikit.siteUrl + '/?rest_route=/aikit/fine-tune/v1/count-tokens',
            data: JSON.stringify({
                pairs: promptCompletionData,
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

    const addPromptCompletionRow = function (prompt = '', completion = '') {
        let row = $('<div class="row mt-2 aikit-pair"></div>');
        let col = $('<div class="col-5"></div>');
        let textarea = $('<textarea class="form-control aikit-prompt" rows="3"></textarea>');
        textarea.val(prompt);
        col.append(textarea);
        row.append(col);

        col = $('<div class="col-5"></div>');
        textarea = $('<textarea class="form-control aikit-completion" rows="3"></textarea>');
        textarea.val(completion);
        col.append(textarea);
        row.append(col);

        col = $('<div class="col-2"></div>');
        let button = $('<button class="btn btn-sm btn-outline-danger aikit-remove-pair"><i class="bi bi-trash"></i></button>');
        col.append(button);
        row.append(col);

        $('.aikit-fine-tune-training-data-manual-inputs').append(row);
    }

    const approveAndStartFineTuning = function (button) {

        // get the prompt/completion data
        let promptCompletionData = [];

        let hasData = false;

        let promptCompletionPairs = $('.aikit-pair');
        promptCompletionPairs.each(function (index, element) {
            let prompt = $(element).find('.aikit-prompt').val();
            let completion = $(element).find('.aikit-completion').val();

            if (prompt === '' && completion === '') {
                return;
            }

            if (prompt !== '' || completion !== '') {
                hasData = true;
            }

            promptCompletionData.push({
                prompt: prompt,
                completion: completion,
            });
        });

        if (!hasData) {
            alert('Please enter at least one prompt/completion pair');
            return;
        }

        button.prepend('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        // disable the button
        button.prop('disabled', true);

        let formData = {
            entered_prompt_completion_pairs: promptCompletionData,
            job_id: $("#aikit-fine-tune-job-id").val(),
            next_step: true,
        }

        $.ajax({
            url: aikit.siteUrl + '/?rest_route=/aikit/fine-tune/v1/edit',
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
        // get the radio button value with name aikit-fine-tune-training-data-source
        let trainingDataSource = $('input[name="aikit-fine-tune-training-data-source"]:checked').val();

        // get the prompt/completion data
        let promptCompletionData = [];

        let hasData = false;

        let promptCompletionPairs = $('.aikit-pair');
        promptCompletionPairs.each(function (index, element) {
            let prompt = $(element).find('.aikit-prompt').val();
            let completion = $(element).find('.aikit-completion').val();

            if (prompt === '' && completion === '') {
                return;
            }

            if (prompt !== '' || completion !== '') {
                hasData = true;
            }

            if (next_step && trainingDataSource === "manual") { // we need to validate the data
                if (prompt === '' && completion !== '' || prompt !== '' && completion === '') {
                    alert('Please enter both prompt and completion.');
                    return;
                }
            }

            promptCompletionData.push({
                prompt: prompt,
                completion: completion,
            });
        });

        if (next_step && !hasData && trainingDataSource === "manual") {
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
            prompt_completion_generation_model: $("#aikit-fine-question-prompt-completion-generation-model").val(),
            prompt_completion_generation_count: $("#aikit-fine-tune-prompt-completion-generation-count").val(),
            prompt_completion_generation_prompt: $("#aikit-fine-tune-generation-prompt").val(),
            training_data_source: trainingDataSource,
            entered_prompt_completion_pairs: promptCompletionData,
            chosen_post_ids: postData,
            job_id: $("#aikit-fine-tune-job-id").val(),
        }

        if (next_step) {
            formData.next_step = true;
        }

        $.ajax({
            url: aikit.siteUrl + '/?rest_route=/aikit/fine-tune/v1/edit',
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

        // get the validatoin errors from data-validation-message attribute

        if ($("#aikit-fine-tune-output-model-name").val() === '') {
            errors.push($("#aikit-fine-tune-output-model-name").data('validation-message'));
        }

        if ($("#aikit-fine-tune-prompt-stop-sequence").val() === '') {
            errors.push($("#aikit-fine-tune-prompt-stop-sequence").data('validation-message'));
        }

        if ($("#aikit-fine-tune-completion-stop-sequence").val() === '') {
            errors.push($("#aikit-fine-tune-completion-stop-sequence").data('validation-message'));
        }

        if (errors.length > 0) {
            alert(errors.join("\n"));
            return;
        }


        button.prepend('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        // disable the button
        button.prop('disabled', true);

        let formData = {
            fine_tune_model: $("#aikit-fine-tune-model").val(),
            fine_tune_output_model_name: $("#aikit-fine-tune-output-model-name").val(),
            fine_tune_prompt_stop_sequence: $("#aikit-fine-tune-prompt-stop-sequence").val(),
            fine_tune_completion_stop_sequence: $("#aikit-fine-tune-completion-stop-sequence").val(),
        };

        $.ajax({
            type: "POST",
            url: aikit.siteUrl + '/?rest_route=/aikit/fine-tune/v1/create',
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

            addPromptCompletionRow(columns[0], columns[1]);
        }
    }

    function convertToCSV(data) {
        let csv = "";

        // Add data rows
        data.forEach(function(item) {
            // add row and each column data show be enclosed in double quotes
            csv +=
                '"' +
                item.prompt.replace(/"/g, '""') +
                '","' +
                item.completion.replace(/"/g, '""') +
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
