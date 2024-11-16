(function($) {
	$(document).ready(
		function() {
			let all_classes = [
				'masterstudy-become-instructor-modal',
				'masterstudy-enterprise-modal',
				'masterstudy-authorization',
				'stm_lms_edit_account',
			];

			$(document).on('drop', '.masterstudy-form-builder-file-upload__field', function(e) {
				e.preventDefault();
				$(this).removeClass('masterstudy-form-builder-file-upload__field_highlight');
				const files = e.originalEvent.dataTransfer.files;
				const dropArea = $(this);
				const parentClass = findClosestParentClassName(dropArea, all_classes);
				handleFileInputChange(dropArea, files[0], parentClass, 'drag');
			});

			$(document).on('change', '.masterstudy-form-builder-file-upload__input', function(e) {
				const dropArea = $(this).closest('.masterstudy-form-builder-file-upload__field');
				const parentClass = findClosestParentClassName(dropArea, all_classes);
				handleFileInputChange(dropArea, this, parentClass, 'input');
			});

			$(document).on('click', '.masterstudy-form-builder-file-upload__link', function(e) {
				if ($(this).hasClass('masterstudy-form-builder-file-upload__link_readonly')) {
					return;
				}

				e.preventDefault();
				const dropArea = $(this).closest('.masterstudy-form-builder-file-upload').find('.masterstudy-form-builder-file-upload__field');
				const parentClass = findClosestParentClassName(dropArea, all_classes);
				const formContainer = $(this).closest(`.${parentClass}`);
				deleteFile($(this).data('id'), dropArea, parentClass, formContainer);
			});

			$(document).on('dragenter', '.masterstudy-form-builder-file-upload__field', function(e) {
				e.preventDefault();
				$(this).addClass('masterstudy-form-builder-file-upload__field_highlight');
			});

			$(document).on('dragover', '.masterstudy-form-builder-file-upload__field', function(e) {
				e.preventDefault();
			});

			$(document).on('dragleave', '.masterstudy-form-builder-file-upload__field', function(e) {
				let rect = this.getBoundingClientRect();
				let x = e.clientX;
				let y = e.clientY;
				if (!(x >= rect.left && x <= rect.right && y >= rect.top && y <= rect.bottom)) {
					$(this).removeClass('masterstudy-form-builder-file-upload__field_highlight');
				}
			});

			$(document).on('click', '.masterstudy-form-builder-file-upload__field-button', function() {
				$(this).parent().find('.masterstudy-form-builder-file-upload__input').click();
			});

			$(document).on('click', "[data-id='cancel'], .masterstudy-alert__header-close", function(e) {
				e.preventDefault();
				$(this).closest("[data-id='form_builder_file_alert']").removeClass('masterstudy-alert_open');
			});

			$('.masterstudy-form-builder__radio-group').each( function() {
				if ( $(this).find('.masterstudy-form-builder__radio-wrapper_checked').length === 0 ) {
					$(this).find('.masterstudy-form-builder__radio-container').first().find('.masterstudy-form-builder__radio-wrapper')
					.addClass('masterstudy-form-builder__radio-wrapper_checked');
				}
			})

			setTimeout(function(){
				$('.masterstudy-form-builder__select').each(
					function() {
						let $parent = $(this).parent();
						$(this).select2({
							dropdownParent: $parent,
							minimumResultsForSearch: Infinity,
						});
					}
				);
			}, 1500);

			$(document).on('click', '.masterstudy-form-builder__checkbox-title', function() {
				$(this).prev().find('.masterstudy-form-builder__checkbox-wrapper').trigger('click');
			});

			$(document).on('click', '.masterstudy-form-builder__checkbox-wrapper', function() {
				$(this).toggleClass('masterstudy-form-builder__checkbox-wrapper_checked');
				let input     = $(this).prev();
				let container = input.closest('.masterstudy-form-builder__checkbox-group');
				input.prop('checked', ! input.prop('checked'));
				if (container.length > 0) {
					container.find('[data-error-id="required"]').remove();
				}
			});

			$(document).on('click', '.masterstudy-form-builder__radio-title', function() {
				$(this).prev().find('.masterstudy-form-builder__radio-wrapper').trigger('click');
			});

			$(document).on('click', '.masterstudy-form-builder__radio-wrapper', function() {
				$(this).addClass('masterstudy-form-builder__radio-wrapper_checked');
				let input = $(this).prev();
				input.prop('checked', true);
				$(this).closest('.masterstudy-form-builder__radio-container').siblings().find('input[type="radio"]').prop('checked', false);
				$(this).closest('.masterstudy-form-builder__radio-container').siblings().find('.masterstudy-form-builder__radio-wrapper').removeClass('masterstudy-form-builder__radio-wrapper_checked');
			});

			const formats = {
				'img': ['image/png','image/jpeg','image/gif','image/svg+xml'],
				'excel': ['application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
				'word': ['application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
				'powerpoint': ['application/vnd.ms-powerpoint','application/vnd.openxmlformats-officedocument.presentationml.presentation'],
				'pdf': ['application/pdf'],
				'video': ['video/mp4','video/avi','video/flv','video/webm','video/x-ms-wmv','video/quicktime'],
				'audio': ['audio/mp3','audio/x-ms-wma','audio/aac','audio/mpeg'],
				'archive': ['application/zip','application/gzip','application/x-rar-compressed','application/x-7z-compressed','application/x-zip-compressed'],
			};

			function findClosestParentClassName(element, classList) {
				let foundClass = null;

				classList.some(className => {
					if (element.closest(`.${className}`).length > 0) {
						foundClass = className;
						return true;
					}
					return false;
				});

				return foundClass;
			}

			function handleFileInputChange(dropArea, fileInput, parent_class, type) {
				if ($(dropArea).parent().find('.masterstudy-form-builder-file-upload__item').length > 0) {
					$(dropArea).parent().find('.masterstudy-form-builder-file-upload__field-error')
					.addClass('masterstudy-form-builder-file-upload__field-error_show')
					.text(masterstudy_form_builder_data.only_one_file);
					return;
				} else {
					$(dropArea).parent().find('.masterstudy-form-builder-file-upload__field-error').removeClass('masterstudy-form-builder-file-upload__field-error_show');
				}

				let file;
				if ( 'drag' === type ) {
					file = fileInput;
				} else {
					file = fileInput.files[0];
				}

				if (file) {
					handleFiles(file, dropArea, parent_class);
				}
				fileInput.value = null;
			}

			function getFileType(fileType) {
				return Object.keys(formats).filter(type => formats[type].includes(fileType));
			}

			function handleFiles(file, dropArea, parent_class) {
				const loadingBar    = $(dropArea).find('.masterstudy-form-builder-file-upload__field-progress-bar-filled');
				const totalFiles    = 1;
				const extensions    = $(dropArea).find('.masterstudy-hint__text').text();
				let uploadedFiles   = 0;
				let current_percent = 0;
				let total_percent   = 0;
				const formData      = new FormData();
				formData.append('file', file);
				formData.append('action', 'stm_lms_upload_form_file');
				formData.append('nonce', masterstudy_form_builder_data.file_upload_nonce);

				if (extensions.length > 0) {
					formData.append('extensions', extensions.trim());
				}

				$.ajax({
					url: masterstudy_form_builder_data.ajax_url,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					xhr: function() {
						const xhr = new window.XMLHttpRequest();
						xhr.upload.addEventListener(
							'progress',
							function(event) {
								if (event.lengthComputable) {
									current_percent = (current_percent === 100) ? 95 : ((event.loaded / event.total) / totalFiles) * 100;
									if (totalFiles === 1) {
										loadingBar.css('width', current_percent + '%');
									}
								}
							},
							false
						);
						return xhr;
					},
					beforeSend: function() {
						$( dropArea ).parent().find('.masterstudy-form-builder-file-upload__field').addClass('masterstudy-form-builder-file-upload__field_loading');
					},
					success: function (data) {
						uploadedFiles++;
						if (data.error === false) {
							file.id = data.id;
							generateFileHtml(file, dropArea);
						}
						if (totalFiles === 1 && data.error !== false) {
							$(dropArea).parent().find('.masterstudy-form-builder-file-upload__field-error').text(data.message);
							$(dropArea).parent().find('.masterstudy-form-builder-file-upload__field-error').addClass('masterstudy-form-builder-file-upload__field-error_show');
						}
						if (totalFiles === uploadedFiles) {
							loadingBar.css('width', '100%');
							setTimeout(
								function() {
									$(dropArea).parent().find('.masterstudy-form-builder-file-upload__field').removeClass('masterstudy-form-builder-file-upload__field_loading');
									loadingBar.css('width', '0');
								},
								500
							);
							setTimeout(
								function() {
									$(dropArea).parent().find('.masterstudy-form-builder-file-upload__field-error').removeClass('masterstudy-form-builder-file-upload__field-error_show');
								},
								1500
							);
							let fields = [];
							if (parent_class === 'masterstudy-enterprise-modal') {
								fields = masterstudy_enterprise_fields;
							} else if (parent_class === 'masterstudy-become-instructor-modal') {
								fields = masterstudy_become_instructor_fields;
							} else if (parent_class === 'masterstudy-authorization') {
								fields = $(dropArea).closest('.masterstudy-authorization__instructor-container').length > 0 ? authorization_data.instructor_fields : authorization_data.additional_fields;
							}
							let fieldToUpdate = fields.find(field => field.slug === $(dropArea).attr('id'));
							if (fieldToUpdate) {
								fieldToUpdate.value = data.url;
							}
							$(dropArea).find('.masterstudy-form-builder-file-upload__input').attr('data-url', data.url);
							$(dropArea).parent().find(`.${parent_class}__form-field-error`).remove();
						} else {
							total_percent = total_percent + current_percent;
							loadingBar.css('width', total_percent + '%');
						}
					}
				});
			}

			function generateFileHtml(file, dropArea) {
				let filesize   = Math.round(file.size / 1024),
				filesize_label = (filesize > 1000) ? 'mb' : 'kb',
				icon           = (getFileType(file.type).length > 0) ? getFileType(file.type) : 'unknown',
				icon_url       = masterstudy_form_builder_data.icon_url + icon + '.svg';
				filesize       = (filesize > 1000) ? Math.round(filesize / 1024) : filesize;
				let html       = `
				<div class="masterstudy-form-builder-file-upload__item">
					<img src="${icon_url}" class="masterstudy-form-builder-file-upload__image">
					<div class="masterstudy-form-builder-file-upload__wrapper">
						<span class="masterstudy-form-builder-file-upload__title">${file.name}</span>
						<span class="masterstudy-form-builder-file-upload__size">${filesize} ${filesize_label}</span>
						<a class="masterstudy-form-builder-file-upload__link" href="#" data-id="${file.id}"></a>
					</div>
				</div>`;
				$(dropArea).parent().find('.masterstudy-form-builder-file-upload__item-wrapper').append(html);
			}

			function deleteFile(id, dropArea, parent_class, formContainer) {
				formContainer.find("[data-id='form_builder_file_alert']").addClass('masterstudy-alert_open');
				formContainer.find("[data-id='form_builder_file_alert']").find("[data-id='submit']").one(
					'click',
					function(e) {
						const formData = new FormData();
						formData.append('file_id', id);
						formData.append('action', 'stm_lms_delete_form_file');
						formData.append('nonce', masterstudy_form_builder_data.file_delete_nonce);

						$.ajax({
							url: masterstudy_form_builder_data.ajax_url,
							type: 'POST',
							data: formData,
							processData: false,
							contentType: false,
							beforeSend: function() {
								formContainer.find("[data-id='form_builder_file_alert']").removeClass('masterstudy-alert_open');
							},
							success: function (data) {
								if (data === 'OK') {
									formContainer.find(`[data-id='${id}']`).closest('.masterstudy-form-builder-file-upload__item').remove();
									$(dropArea).parent().find('.masterstudy-form-builder-file-upload__field-error').removeClass('masterstudy-form-builder-file-upload__field-error_show');
									let fields = [];
									if (parent_class === 'masterstudy-enterprise-modal') {
										fields = masterstudy_enterprise_fields;
									} else if (parent_class === 'masterstudy-become-instructor-modal') {
										fields = masterstudy_become_instructor_fields;
									} else if (parent_class === 'masterstudy-authorization') {
										fields = $(dropArea).closest('.masterstudy-authorization__instructor-container').length > 0 ? authorization_data.instructor_fields : authorization_data.additional_fields;
									}
									let fieldToUpdate = fields.find(field => field.slug === $(dropArea).attr('id'));
									if (fieldToUpdate) {
										fieldToUpdate.value = '';
									}
									$(dropArea).find('.masterstudy-form-builder-file-upload__input').attr('data-url', '');
								}
							}
						});
					}
				);
			}
		}
	)
})(jQuery);