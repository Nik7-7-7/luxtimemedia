(function () {
	'use strict';

	(function ($) {
	  const exportButton = $('.mpa-export-button');
	  const cancelExportButton = $('.mpa-cancel-export-button');
	  const exportProgressBar = $('.mpa-export-progress-bar');
	  let isExportStarted = false;
	  let isCancelExportStarted = false;
	  let isExportDownloaded = false;
	  exportButton.click(event => {
	    event.stopPropagation();
	    event.preventDefault();
	    if (!isExportStarted) {
	      // prevent double click processing
	      isExportStarted = true;
	      isExportDownloaded = false;
	      doAjaxRequest('start');
	    }
	  });
	  cancelExportButton.click(event => {
	    event.stopPropagation();
	    event.preventDefault();
	    if (!isCancelExportStarted) {
	      // prevent double click processing
	      isCancelExportStarted = true;
	      doAjaxRequest('cancel');
	    }
	  });

	  // check status if we have already started export
	  doAjaxRequest();
	  function doAjaxRequest(exportCommand = 'get_status') {
	    let requestData = {
	      action: 'mpa_export_bookings',
	      wp_nonce: mpaAjaxData.wpNonces['mpa_export_bookings'],
	      command: exportCommand
	    };
	    if ('start' === exportCommand) {
	      if (exportButton.attr('data-booking_status')) {
	        requestData.booking_status = exportButton.attr('data-booking_status');
	      }
	      if (exportButton.attr('data-service_date_from')) {
	        requestData.service_date_from = exportButton.attr('data-service_date_from');
	      }
	      if (exportButton.attr('data-service_date_to')) {
	        requestData.service_date_to = exportButton.attr('data-service_date_to');
	      }
	      if (exportButton.attr('data-service_id')) {
	        requestData.service_id = exportButton.attr('data-service_id');
	      }
	      if (exportButton.attr('data-employee_id')) {
	        requestData.employee_id = exportButton.attr('data-employee_id');
	      }
	      if (exportButton.attr('data-location_id')) {
	        requestData.location_id = exportButton.attr('data-location_id');
	      }
	    }
	    console.log('REQUEST: ', requestData);
	    $.ajax({
	      url: mpaAjaxData.ajaxUrl,
	      type: 'POST',
	      dataType: 'json',
	      data: requestData,
	      success: function (response) {
	        // allow button clicks
	        isExportStarted = false;
	        isCancelExportStarted = false;
	        console.log('RESPONSE DATA: ', response.data);
	        if ('canceled' === response.data.status || 'finished' === response.data.status && !response.data.exportFileUrl) {
	          exportButton.removeClass('mpa-hide');
	          cancelExportButton.addClass('mpa-hide');
	          exportProgressBar.addClass('mpa-hide');
	        } else {
	          exportButton.addClass('mpa-hide');
	          cancelExportButton.removeClass('mpa-hide');
	          exportProgressBar.removeClass('mpa-hide');
	          exportProgressBar.val(response.data.percentage);
	          exportProgressBar.html(response.data.percentage + '%');

	          // do we have file for download
	          if ('finished' === response.data.status && response.data.exportFileUrl) {
	            exportButton.removeClass('mpa-hide');
	            cancelExportButton.addClass('mpa-hide');
	            exportProgressBar.addClass('mpa-hide');
	            if (!isExportDownloaded) {
	              isExportDownloaded = true;
	              window.location = response.data.exportFileUrl;
	            }
	          } else if ('finished' !== response.data.status && 'canceled' !== response.data.status) {
	            if ('start' === exportCommand) {
	              // second request do right after first one
	              // to get first percentage quickly
	              doAjaxRequest();
	            } else {
	              // prepare next get_status request
	              setTimeout(() => {
	                doAjaxRequest();
	              }, 3000);
	            }
	          }
	        }
	      },
	      error: response => {
	        // allow button clicks
	        isExportStarted = false;
	        isCancelExportStarted = false;
	        if (undefined !== response.responseJSON && undefined !== response.responseJSON.data.errorMessage) {
	          console.error(response.responseJSON.data.errorMessage);
	        } else {
	          console.error(response);
	        }
	      }
	    });
	  }
	})(jQuery);

})();
