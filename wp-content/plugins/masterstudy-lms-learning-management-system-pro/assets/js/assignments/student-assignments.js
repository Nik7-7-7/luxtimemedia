(function ($) {
  let mediaUploader;
  let attachmentIDs = [];
  let mediaStreams = [];
  let disableBtnAction = false;
  let videoCounter = 1;
  let audioCounter = 1;

  const apiEndpoint = stm_student_assignments.mediaAPI;
  const messages = stm_student_assignments.message;
  const uploaderBtn = $('[data-id="masterstudy-file-upload-field"]');
  const alertBtn = $("[data-id='assignment_file_alert']");
  const recordAudioBtn = $('[data-id="masterstudy-audio-recorder"]');
  const recordVideoBtn = $('[data-id="masterstudy-video-recorder"]');

  uploaderBtn.on("click", function (e) {
    e.preventDefault();

    if (mediaUploader) {
      mediaUploader.open();
      return;
    }

    mediaUploader = wp.media.frames.file_frame = wp.media({
      multiple: false,
    });

    mediaUploader.on("select", function () {
      const attachment = mediaUploader
        .state()
        .get("selection")
        .first()
        .toJSON();

      $.ajax({
        type: "POST",
        url: ajaxurl,
        data: {
          action: "stm_lms_add_assignment_attachment",
          attachment: attachment,
          attachment_id: attachment.id,
          is_review: true,
          nonce: stm_student_assignments.nonce,
          post_id: getPostID(),
        },
        success: function (res) {
          if (res.success === true) {
            generateFileHtml(attachment, res.data);
          }
        },
      });
    });

    mediaUploader.open();
  });

  // On delete call alert
  $(document).on(
    "click",
    ".masterstudy-file-attachment__delete",
    function (event) {
      event.preventDefault();
      alertBtn.addClass("masterstudy-alert_open");
      deleteAttachment(this, $(this).data("id"));
    }
  );
  //cancel alert for delete file
  alertBtn.find("[data-id='cancel']").click(function (e) {
    e.preventDefault();
    alertBtn.removeClass("masterstudy-alert_open");
  });
  alertBtn.find(".masterstudy-alert__header-close").click(function (e) {
    e.preventDefault();
    alertBtn.removeClass("masterstudy-alert_open");
  });
  //Audio Player for audio recorder
  MasterstudyAudioPlayer.init({
    selector: ".masterstudy-audio-player",
    showDeleteButton: false,
    showDownloadButton: true,
  });

  recordAudioBtn.on("click", function (e) {
    e.preventDefault();

    if (disableBtnAction) return false;

    const recorder = $(".masterstudy-audio__recorder");
    $('.masterstudy-message[data-id="message-box"]').addClass(
      "masterstudy-message_hidden"
    );
    switchButtonState([recordAudioBtn, uploaderBtn, recordVideoBtn]);

    const audioRecorder = new MasterstudyAudioRecorder(
      ".masterstudy-audio__recorder",
      {
        isHidden: true,
        directRecording: true,
      }
    );

    audioRecorder.startRecording().then(function (isAllowed) {
      if (isAllowed) {
        recorder.removeClass("masterstudy-audio__recorder_hidden");
      } else {
        messageHandler(messages.audio.permission, "error");
        switchButtonState([recordAudioBtn, uploaderBtn, recordVideoBtn]);
      }
    });

    disableBtnAction = true;

    const progressBar = $(".masterstudy-assignment__instructor-review__controls-items .masterstudy-progress");
    progressBar.find(".masterstudy-progress__bar-filled").css("width", "0%");

    audioRecorder.addAction("beforeStop", function (recorder) {
      recorder.hideRecorder();
      progressBar.removeClass("masterstudy-progress_hidden");
    });

    audioRecorder.addAction("onStop", function (audioBlob, mediaStream) {
      disableBtnAction = false;

      if (!mediaStream || mediaStreams.indexOf(mediaStream.id) !== -1) return;
      mediaStreams.push(mediaStream.id);

      const uniqID = mediaStream.id.slice(-6) + audioCounter;
      const fileUrl = window.URL.createObjectURL(audioBlob);
      const postID = getPostID();
      const fileName = `audio-attachment-${postID}-${uniqID}.mp3`;
      const audioFile = new File([audioBlob], fileName, {
        type: audioBlob.type,
      });

      audioCounter++;

      const formData = new FormData();
      formData.append("file", audioFile);
      recorder.addClass("masterstudy-audio__recorder_hidden");
      switchButtonState([recordAudioBtn, uploaderBtn, recordVideoBtn]);

      $.ajax({
        url: apiEndpoint,
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        xhr: function () {
          var xhr = new window.XMLHttpRequest();
          xhr.upload.addEventListener(
            "progress",
            function (evt) {
              if (evt.lengthComputable) {
                let currentPercent = Math.round((evt.loaded / evt.total) * 100);
                currentPercent = currentPercent >= 95 ? 95 : currentPercent;
                // Update progress bar heres
                progressBar
                  .find(".masterstudy-progress__percent")
                  .text(currentPercent);
                progressBar
                  .find(".masterstudy-progress__bar-filled")
                  .css("width", currentPercent + "%");
              }
            },
            false
          );
          return xhr;
        },
        headers: {
          "X-WP-Nonce": ms_lms_nonce,
          Accept: "application/json",
        },
        error: function (xhr, status, error) {
          messageHandler(
            messages.error.text,
            "error",
            messages.audio.download,
            fileUrl
          );
          switchButtonState([
            recordAudioBtn,
            attachFileBtn,
            recordVideoBtn,
            submitBtn,
          ]);
        },
        complete: function (xhr, status) {
          if ("success" === status) {
            setTimeout(function () {
              progressBar.find(".masterstudy-progress__percent").text(100);
              progressBar
                .find(".masterstudy-progress__bar-filled")
                .css("width", "100%");
            }, 1000);
            setTimeout(function () {
              const attachment = xhr.responseJSON;
              progressBar.addClass("masterstudy-progress_hidden");
              if (attachment) {
                if (
                  attachment.id > 0 &&
                  attachmentIDs.indexOf(attachment.id) === -1
                ) {
                  attachmentIDs.push(attachment.id);
                  addAttachmentToPost(attachment, true);
                }
              }
            }, 1500);
          } else {
            messageHandler(
              messages.error.text,
              "error",
              messages.audio.download,
              fileUrl
            );
            switchButtonState([
              recordAudioBtn,
              attachFileBtn,
              recordVideoBtn,
              submitBtn,
            ]);
          }
        },
      });
    });
  });

  recordVideoBtn.on("click", function (e) {
    e.preventDefault();

    if (disableBtnAction) return false;

    $('.masterstudy-message[data-id="message-box"]').addClass(
      "masterstudy-message_hidden"
    );
    switchButtonState([recordAudioBtn, uploaderBtn, recordVideoBtn]);

    const videoRecorder = new MasterstudyVideoRecoder(
      ".masterstudy-video__recorder",
      {
        isHidden: true,
        clearSource: true,
      }
    );

    videoRecorder.startRecording().then(function (isAllowed) {
      if (isAllowed) {
        videoRecorder.showRecorder();
      } else {
        messageHandler(messages.video.permission, "error");
        switchButtonState([recordAudioBtn, uploaderBtn, recordVideoBtn]);
      }
    });

    disableBtnAction = true;

    videoRecorder.addAction("onStop", function (videoBlob, mediaStream) {
      disableBtnAction = false;
      videoRecorder.hideRecorder();

      if (!mediaStream || mediaStreams.indexOf(mediaStream.id) !== -1) return;
      mediaStreams.push(mediaStream.id);

      const uniqID = mediaStream.id.slice(-6) + videoCounter;
      const fileUrl = window.URL.createObjectURL(videoBlob);
      const fileName = `video-attachment-${getPostID()}-${uniqID}.mp4`;
      const videoFile = new File([videoBlob], fileName, {
        type: videoBlob.type,
      });

      videoCounter++;

      const formData = new FormData();
      formData.append("file", videoFile);

      switchButtonState([recordAudioBtn, uploaderBtn, recordVideoBtn]);

      const progressBar = $(".masterstudy-assignment__instructor-review__controls-items .masterstudy-progress");
      progressBar.removeClass("masterstudy-progress_hidden");
      progressBar.find(".masterstudy-progress__bar-filled").css("width", "0%");

      $.ajax({
        url: apiEndpoint,
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        xhr: function () {
          var xhr = new window.XMLHttpRequest();
          xhr.upload.addEventListener(
            "progress",
            function (evt) {
              if (evt.lengthComputable) {
                let currentPercent = Math.round((evt.loaded / evt.total) * 100);
                currentPercent = currentPercent >= 95 ? 95 : currentPercent;
                // Update progress bar here
                progressBar
                  .find(".masterstudy-progress__percent")
                  .text(currentPercent);
                progressBar
                  .find(".masterstudy-progress__bar-filled")
                  .css("width", currentPercent + "%");
              }
            },
            false
          );
          return xhr;
        },
        headers: {
          "X-WP-Nonce": ms_lms_nonce,
          Accept: "application/json",
        },
        error: function (xhr, status, error) {
          messageHandler(
            messages.error.text,
            "error",
            messages.video.download,
            fileUrl
          );
          switchButtonState([
            recordAudioBtn,
            attachFileBtn,
            recordVideoBtn,
            submitBtn,
          ]);
        },
        complete: function (xhr, status) {
          if ("success" === status) {
            setTimeout(function () {
              progressBar.find(".masterstudy-progress__percent").text(100);
              progressBar
                .find(".masterstudy-progress__bar-filled")
                .css("width", "100%");
            }, 1000);
            setTimeout(function () {
              const attachment = xhr.responseJSON;
              progressBar.addClass("masterstudy-progress_hidden");
              if (attachment) {
                if (
                  attachment.id > 0 &&
                  attachmentIDs.indexOf(attachment.id) === -1
                ) {
                  attachmentIDs.push(attachment.id);
                  addAttachmentToPost(attachment, true);
                }
              }
            }, 1500);
          } else {
            messageHandler(
              messages.error.text,
              "error",
              messages.video.download,
              fileUrl
            );
            switchButtonState([
              recordAudioBtn,
              attachFileBtn,
              recordVideoBtn,
              submitBtn,
            ]);
          }
        },
      });
    });
  });

  function switchButtonState(button, disableClass) {
    disableClass = disableClass || "masterstudy-button_disabled";
    if (
      button instanceof NodeList ||
      button instanceof HTMLCollection ||
      Array.isArray(button)
    ) {
      button.forEach(function (btn) {
        switchButtonState(btn);
      });
    } else {
      button.toggleClass(disableClass);
    }
  }

  function messageHandler(message, type = "success", downloadText, file_url) {
    const messageBox = $('.masterstudy-message[data-id="message-box"]');
    const messageIcon = messageBox.find(".masterstudy-message__icon");
    const messageText = messageBox.find(".masterstudy-message__text");
    const messageLink = messageBox.find(".masterstudy-message__link");

    messageIcon.attr("class", "");
    if ("success" === type) {
      messageBox.removeClass(
        "masterstudy-message_color-danger masterstudy-message_bg-danger"
      );
      messageBox.addClass(
        "masterstudy-message_color-success masterstudy-message_bg-success"
      );
      messageIcon.addClass("masterstudy-message__icon stmlms-check");
    } else {
      $(".masterstudy-loader").hide();
      messageBox.removeClass(
        "masterstudy-message_color-success masterstudy-message_bg-success"
      );
      messageBox.addClass(
        "masterstudy-message_color-danger masterstudy-message_bg-danger"
      );
      messageIcon.addClass("masterstudy-message__icon stmlms-warning");

      if (downloadText) {
        messageLink.html(
          `<span class="stmlms-download"></span> ${downloadText}`
        );
        messageLink.attr("href", file_url);
        messageLink.attr("download", "assignment_review_media");
      }
    }
    messageText.html(message);
    $('.masterstudy-message[data-id="message-box"]').removeClass(
      "masterstudy-message_hidden"
    );
  }

  function addAttachmentToPost(attachment, isCreated) {
    let isAdded = false;
    isCreated =
      isCreated === null || isCreated === undefined ? false : isCreated;
    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: {
        action: "stm_lms_add_assignment_attachment",
        attachment: attachment,
        attachment_id: attachment.id,
        is_review: true,
        nonce: stm_student_assignments.nonce,
        post_id: getPostID(),
        is_created: isCreated,
      },
      success: function (res) {
        if (res.success === true) {
          isAdded = true;
          generateFileHtml(attachment, res.data);
          $(".masterstudy-loader").hide();
        }
      },
    });

    return isAdded;
  }

  function getFileType(url, formats) {
    const fileExtension = url.split(".").pop();
    for (let fileType in formats) {
      if (formats[fileType].includes(fileExtension)) {
        return fileType;
      }
    }
    return "unknown";
  }

  function getPostID() {
    const queryString = window.location.search.substring(1);
    const urlParams = new URLSearchParams(queryString);
    return urlParams.get("post") || 0;
  }

  function deleteAttachment(deleteBtn, attachmentID) {
    alertBtn.find("[data-id='submit']").click(function (e) {
      e.preventDefault();

      $.ajax({
        type: "POST",
        url: ajaxurl,
        data: {
          action: "stm_lms_delete_assignment_attachment",
          attachment_id: attachmentID,
          nonce: stm_student_assignments.nonce,
          post_id: getPostID(),
          is_review: true,
        },
        beforeSend: function () {
          alertBtn.removeClass("masterstudy-alert_open");
        },
        success: function (res) {
          if (res.success === true) {
            $(deleteBtn).parents(".masterstudy-file-attachment").remove();
          }
        },
      });
    });
  }

  // add saved file to list on page
  function generateFileHtml(attachment, data) {
    let attachmentUrl = attachment.url || attachment.source_url;
    let fileType = getFileType(attachmentUrl, data.files_formats);
    let attachmentTitle = attachment.title;

    if (typeof attachmentTitle === "object") {
      attachmentTitle = attachment.title.raw;
    }

    let labeledFilesize = "";
    if (attachment.media_details) {
      let filesize = Math.round(attachment.media_details.filesize / 1024);
      let filesize_label = filesize > 1000 ? "mb" : "kb";
      filesize = filesize > 1000 ? Math.round(filesize / 1024) : filesize;
      labeledFilesize = filesize + " " + filesize_label;
    }
    if (attachment.filesizeHumanReadable) {
      labeledFilesize = attachment.filesizeHumanReadable.toLowerCase();
    }

    const attachmentTemplate = $(
      '.masterstudy-assignment__instructor-review__controls-items [data-id="masterstudy-file-attachment__template"]'
    ).clone();
    const audioPlayer = attachmentTemplate.find(".masterstudy-audio-player");
    const videoPlayer = attachmentTemplate.find(".masterstudy-video__player");

    attachmentTemplate
      .find("img")
      .attr("src", `${data.icon_url + fileType}.svg`);
    attachmentTemplate
      .find(".masterstudy-file-attachment__title")
      .html(attachmentTitle);
    attachmentTemplate
      .find(".masterstudy-file-attachment__size")
      .html(labeledFilesize);
    attachmentTemplate
      .find(".masterstudy-file-attachment__delete")
      .attr("data-id", attachment.id);

    if (fileType === "audio") {
      audioPlayer.attr("data-id", `masterstudy-audio-player-${attachment.id}`);
      audioPlayer.find("audio").attr("src", attachmentUrl);
      audioPlayer.find("source").attr("src", attachmentUrl);
      audioPlayer
        .find(".masterstudy-audio-player__download-link")
        .attr("href", attachmentUrl);
      audioPlayer.removeClass("masterstudy-audio-player_hidden");
      videoPlayer.addClass("masterstudy-video__player--hidden");
    }

    if (fileType === "video") {
      videoPlayer.attr("src", attachmentUrl);
      videoPlayer.find("source").attr("src", attachmentUrl);
      videoPlayer.removeClass("masterstudy-video__player--hidden");
      audioPlayer.addClass("masterstudy-audio-player_hidden");
    }

    $(".masterstudy-assignment__instructor-attachments").append(
      attachmentTemplate.prop("outerHTML")
    );

    if (fileType === "audio") {
      MasterstudyAudioPlayer.init({
        selector: `[data-id="masterstudy-audio-player-${attachment.id}"]`,
        showDeleteButton: false,
      });
    }
  }
})(jQuery);
