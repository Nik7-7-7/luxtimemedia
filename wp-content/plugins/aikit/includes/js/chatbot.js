"use strict";
jQuery(function($) {

    let mediaRecorder;
    let silenceThreshold = 1; // Adjust this value based on your environment
    let silenceCheckInterval;
    let recordingStartTimestamp;
    let mediaStream;
    const minRecordingDuration = 4000;
    const maxRecordingDuration = 30000;
    let isRecording = false;

    const startRecordingButton = $("#aikit-voice-button");
    // const audioPlayer = document.getElementById('audioPlayer');

    startRecordingButton.click(function (event) {
        event.preventDefault();

        if (mediaStream == null) {
            navigator.mediaDevices.getUserMedia({audio: true})
                .then((stream) => {
                    mediaStream = stream;
                    handleRecording();
                })
                .catch((error) => {
                    // get data-validation-no-mic attribute on .aikit-chat-widget and show the error
                    let message = $(".aikit-chat-widget").data('validation-no-mic');
                    alert(message);
                });
        } else {
            stopRecording();
        }
    });

    const handleRecording = () => {

        isRecording = true;

        const audioContext = new AudioContext();
        const mediaStreamSource = audioContext.createMediaStreamSource(mediaStream);
        const analyser = audioContext.createAnalyser();
        mediaStreamSource.connect(analyser);

        // mediaRecorder = new MediaRecorder(mediaStream);
        mediaRecorder = new RecordRTC(mediaStream, {
            type: 'audio',
            mimeType: 'audio/webm',
            numberOfAudioChannels: 1,
            recorderType: RecordRTC.StereoAudioRecorder,
            checkForInactiveTracks: true,
            desiredSampRate: 16000,
        });

        mediaRecorder.startRecording();

        recordingStartTimestamp = Date.now();

        // Delay the start of silence checking
        setTimeout(() => {
            silenceCheckInterval = setInterval(checkSilence, 180); // Adjust the interval as needed
        }, minRecordingDuration);

        startRecordingButton.attr('data-recording', '1');

        const checkSilence = () => {
            const dataArray = new Uint8Array(analyser.frequencyBinCount);
            analyser.getByteFrequencyData(dataArray);

            const average = dataArray.reduce((acc, val) => acc + val, 0) / dataArray.length;

            if (
                (average <= silenceThreshold && (Date.now() - recordingStartTimestamp) > minRecordingDuration) ||
                ((Date.now() - recordingStartTimestamp) > maxRecordingDuration)
            ) {
                stopRecording();
            }

        };
    };

    const stopRecording = () => {
        if (!isRecording) {
            return;
        }

        clearInterval(silenceCheckInterval);
        mediaRecorder.stopRecording(() => {
            isRecording = false;
            let audioBlob = mediaRecorder.getBlob();

            sendAudioToServer(audioBlob);
            mediaStream.getTracks().forEach(track => track.stop());

            startRecordingButton.removeAttr('data-recording');

            mediaStream = null;
        });
    }

    const sendAudioToServer = (audioBlob) => {

        $(".aikit-mic-icon").hide();
        $(".aikit-voice-button .aikit-loading").attr('data-visible', '1');

        let formData = new FormData();

        let file = new File([audioBlob], "audio.webm", { type: 'audio/webm' });

        formData.append('audio', file);

        $.ajax({
            url: aikitChatbot.siteUrl + '/?rest_route=/aikit/chatbot/v1/speech-to-text',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $("#aikit-new-message-textarea").val($("#aikit-new-message-textarea").val() + response.message);
                $(".aikit-mic-icon").show();
                $(".aikit-voice-button .aikit-loading").attr('data-visible', '0');
            },
            error: function(error) {
                alert(error);

                $(".aikit-mic-icon").show();
                $(".aikit-voice-button .aikit-loading").attr('data-visible', '0');
            }
        });
    };


    aikitChatbot.previousMessages = [];
    let isLivePreview = false;

    // deactivate all inputs inside .aikit-deactivated-form class
    $(".aikit-deactivated-form *").prop('disabled', true);

    $(".aikit-chatbot-send-button").click(function (event) {
        event.preventDefault();

        sendChatbotMessage();
    });

    // on enter key press, send the message
    $("#aikit-new-message-textarea").keypress(function (event) {
        // if shift key is pressed, do nothing
        if (event.shiftKey) {
            return;
        }

        if (event.keyCode === 13) {
            event.preventDefault();
            sendChatbotMessage();
        }
    });

    $(".aikit-open-chat-button").click(function(){
        $(".aikit-chat-container").fadeIn(100, function () {
            $(".aikit-open-chat-button").fadeOut(100);
            $(".aikit-chatbot-send-button").fadeIn(100);
            $(".aikit-chat-widget").addClass('aikit-open');
        });

    });
    $(".aikit-exit-chat").click(function(){
        $(".aikit-chat-container").fadeOut(100, function () {
            $(".aikit-open-chat-button").fadeIn(100);
            $(".aikit-chatbot-send-button").fadeOut(100);
            $(".aikit-chat-widget").removeClass('aikit-open');
        });
    });

    if ($('.aikit-chat-widget').data('default-view') === 'collapsed') {
        $(".aikit-chat-container").hide();
    } else {
        $(".aikit-open-chat-button").hide();
        $(".aikit-chat-widget").addClass('aikit-open');
    }

    const sendChatbotMessage = () => {
        let message = $("#aikit-new-message-textarea").val();

        if (message === '') {
            return;
        }

        if ($(".aikit-chatbot-send-button").prop('disabled')) {
            return;
        }

        // deactivate the send button
        $(".aikit-chatbot-send-button").prop('disabled', true);
        $(".aikit-chatbot-send-button").css('opacity', '0.5');

        addMessageToChat(message, "reply");

        let loading = $('<div class="aikit-message aikit-message-bot">\n' +
            '                                    <span class="aikit-message-content">\n' +
            '                                        <span class="aikit-typing">\n' +
            '                                            <span class="aikit-dot"></span>\n' +
            '                                            <span class="aikit-dot"></span>\n' +
            '                                            <span class="aikit-dot"></span>\n' +
            '                                        </span>\n' +
            '                                    </span>\n' +
            '                                </div>');

        $("#aikit-messages").append(loading);
        loading.hide().fadeIn(300);

        // scroll to bottom of the chat
        $("#aikit-conversation").animate({ scrollTop: $("#aikit-conversation").prop("scrollHeight")}, 50);

        if (isLivePreview) {
            // add a test message
            // wait 1 second
            setTimeout(function () {
                loading.remove();
                addMessageToChat("This is a test message from the live preview.");
                // reactivate the send button
                $(".aikit-chatbot-send-button").prop('disabled', false);

                $(".aikit-chatbot-send-button").css('opacity', '1');

            }, 2000);

            $("#aikit-new-message-textarea").val('');
            return;
        }

        $.ajax({
            type: "POST",
            url: aikitChatbot.siteUrl + '/?rest_route=/aikit/chatbot/v1/chat',
            data: JSON.stringify({
                message: message,
                // json previous messages
                previous_messages: JSON.stringify(aikitChatbot.previousMessages),
                page_content: aikitChatbot.pageContent,
                chat_id: $(".aikit-chat-widget").data('chat-id'),
            }),
            dataType: "json",
            encode: true,
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': aikitChatbot.nonce,
            },
        }).done(function (data) {
            aikitChatbot.previousMessages.push({
                message: message,
                author: 'user',
            });

            aikitChatbot.previousMessages.push({
                message: data.message,
                author: 'assistant',
            });

            data.message = data.message.replace(/\n/g, '<br>');

            data.message = data.message.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank">$1</a>');

            loading.remove();
            addMessageToChat(data.message);

            // reactivate the send button
            $(".aikit-chatbot-send-button").prop('disabled', false);
            $(".aikit-chatbot-send-button").css('opacity', '1');

        }).fail(function (data) {
            if (data.responseJSON) {
                data = data.responseJSON;
            }

            if (data.show_in_alert === true) {
                alert(data.message);
            } else {
                console.log(data);
            }

            addMessageToChat(data.chat_error, "error");

            // reactivate the send button
            $(".aikit-chatbot-send-button").prop('disabled', false);
            $(".aikit-chatbot-send-button").css('opacity', '1');
            loading.remove();
        });

        // clear the input
        $("#aikit-new-message-textarea").val('');
    }

    const addMessageToChat = (message, type = "") => {
        let className = "aikit-message-bot";
        if (type === "reply") {
            className = "aikit-message-user";
        } else if (type === "error") {
            className = "aikit-message-error";
        }

        let bubble = $('<div class="aikit-message  ' + className + '"><span className="aikit-message-content">' + message + '</span></div>');
        $("#aikit-messages").append(bubble);
        bubble.hide().fadeIn(100);
        $("#aikit-conversation").animate({ scrollTop: $("#aikit-conversation").prop("scrollHeight")}, 50);
    }

    const generateBotStyles = () => {
        let secondaryColor = hashColorToRgb(aikitChatbot.secondaryColor, 1);
        let mainColor = hashColorToRgb(aikitChatbot.mainColor, 1);
        let shadowColor = hashColorToRgb(aikitChatbot.mainColor, .6);
        let loadingColor = hashColorToRgb(aikitChatbot.mainColor, .7);
        let dot1 = hashColorToRgb(aikitChatbot.mainColor, .7);
        let dot2 = hashColorToRgb(aikitChatbot.mainColor, .4);
        let dot3 = hashColorToRgb(aikitChatbot.mainColor, .2);

        let titleColor = hashColorToRgb(aikitChatbot.titleColor, 0.9);

        // remove the style if it exists
        $("#aikit-chatbot-style").remove();

        // output bot style to the head
        let style = '<style id="aikit-chatbot-style">';

        style += '.aikit-message-bot {';
        style += '    background-color: ' + aikitChatbot.aiMessageBubbleColor + ';';
        style += '    color: ' + aikitChatbot.aiMessageTextColor + ';';
        style += '}';

        style += '.aikit-chat-bubble a {';
        style += '    text-decoration: underline;';
        style += '}';

        style += '.aikit-message-user {';
        style += '    background: linear-gradient(135deg, ' + aikitChatbot.userMessageBubbleColor + ',' + secondaryColor + ');';
        style += '    color: ' + aikitChatbot.userMessageTextColor + ';';
        style += '}';

        style += '.aikit-chat-header {';
        style += '    background: linear-gradient(135deg, ' + mainColor + ' 0%,' + secondaryColor + ' 100%);';
        style += '    color: ' + aikitChatbot.titleColor + ';';
        style += '}';

        style += '.aikit-button-body {';
        style += '    background: linear-gradient(135deg, ' + mainColor + ',' + secondaryColor + ');';
        style += '    box-shadow: ' + shadowColor + ' 0px 4px 24px;;';
        style += '}';

        style += '.aikit-button i svg, .aikit-down-arrow {';
        style += '    color: ' + titleColor + ';';
        style += '}';


        style += '.aikit-typing .aikit-dot {';
        style += '    background-color: ' + loadingColor + ';';
        style += '}';

        style += ' @keyframes mercuryTypingAnimation {';
        style += '    0% {';
        style += '        transform: translateY(0px);';
        style += '        background-color: ' + dot1 + ';';
        style += '    }';
        style += '    28% {';
        style += '        transform: translateY(-7px);';
        style += '        background-color: ' + dot2 + ';';
        style += '    }';
        style += '    44% {';
        style += '        transform: translateY(0px);';
        style += '        background-color: ' + dot3 + ';';
        style += '    }';
        style += '}';

        style += '</style>';

        $('head').append(style);
    }

    const activateLivePreview = () => {
        isLivePreview = true;
        // activate live editing
        $("input").on('input', function() {
            let setting = $(this).attr('data-setting');
            if (aikitChatbot[setting] !== undefined) {
                aikitChatbot[setting] = $(this).val();
            }

            updateLivePreview();
        });

        updateLivePreview();
    }

    const hashColorToRgb = (color, opacity) => {
        // turn # color to rgb and add opacity
        let colorRgb = color.replace('#', '');
        colorRgb = colorRgb.match(/.{1,2}/g);
        colorRgb = 'rgba(' + parseInt(colorRgb[0], 16) + ',' + parseInt(colorRgb[1], 16) + ',' + parseInt(colorRgb[2], 16) + ', ' + opacity + ')';

        return colorRgb;
    }

    const updateLivePreview = () => {

        generateBotStyles();

        // update get value from #aikit-chatbot-appearance-title and set it to the title
        let title = $("#aikit-chatbot-appearance-title").val();
        $(".aikit-chat-welcome span").html(title);

        // same for first message
        let firstMessage = $("#aikit-chatbot-appearance-start-message").val();
        $(".aikit-message:first").html(firstMessage);

        let inputTextPlaceholder = $("#aikit-chatbot-appearance-input-placeholder").val();
        $("#aikit-new-message-textarea").attr('placeholder', inputTextPlaceholder);
    }

    // on load, generate the bot styles
    generateBotStyles();

    $('.aikit-chat-widget').removeClass('d-none');

    if (window.location.href.indexOf('page=aikit_chatbot') > -1) {
        activateLivePreview();
    }
});
