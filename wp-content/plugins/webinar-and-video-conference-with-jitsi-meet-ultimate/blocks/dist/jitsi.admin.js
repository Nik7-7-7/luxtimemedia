document.addEventListener("DOMContentLoaded", function() {

	document.querySelectorAll('.jitsi-field-switch input').forEach(function(element){
		element.addEventListener('change', function(e) {
			if(e.currentTarget.checked){
				element.closest(".jitsi-field-switch").classList.add('active');
			} else {
				element.closest(".jitsi-field-switch").classList.remove('active');
			}
		});
	});

	var jitsi_img_frame = [];
	document.addEventListener("click", e => {
		var el = ".jitsi-uploader-button, .jitsi-uploader-button *";
        var resetEl = ".jitsi-uploader-reset, .jitsi-uploader-reset *";
        var copyEl = '.jitsi-copy-sc, .jitsi-copy-sc *';

		if (e.target.matches(el)) {
            var that = e.target;
            var fieldForValue = that.getAttribute('data-for');
            
            // If the frame already exists, re-open it.
            if (jitsi_img_frame[fieldForValue]) {
                jitsi_img_frame[fieldForValue].open();
                return;
            }

            // Sets up the media library frame
            jitsi_img_frame[fieldForValue] = wp.media.frames.jitsi_img_frame = wp.media({
                title: 'Select image',
                button: {text: 'Use this image'},
                library: {type: 'image'}
            });

            // Runs when an image is selected.
            jitsi_img_frame[fieldForValue].on('select', function () {
                var media_attachment = jitsi_img_frame[fieldForValue].state().get('selection').first().toJSON();
                document.getElementById(fieldForValue).value = media_attachment.url;
                if(document.getElementById(fieldForValue + '-prev') !== null){
                    document.getElementById(fieldForValue + '-prev').setAttribute('src', media_attachment.url);
                } else {
                    document.getElementById(fieldForValue).parentNode.parentNode.insertAdjacentHTML('afterbegin', `<div id="${fieldForValue}-prev-wrap" class="jitsi-uploader-preview-img"><img  id="${fieldForValue}-prev" src="${media_attachment.url}" alt="Preview Image Upload"/></div>`);
                }
            });
            // Opens the media library frame.
            jitsi_img_frame[fieldForValue].open();
        }

        if(e.target.matches(resetEl)) {
            var that = e.target;
            var fieldForValue = that.getAttribute('data-for');
            var elem = document.getElementById(fieldForValue + '-prev-wrap');

            document.getElementById(fieldForValue).value = '';
            elem.parentNode.removeChild(elem);
        }

        if(e.target.matches(copyEl)){
            e.preventDefault();
            var that = e.target;
            var toCopy = that.getAttribute('data-for');
            var copyText = document.getElementById(toCopy);
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");
            document.getElementById('jitsi-shortcode-copied').classList.add('fade-in');
            setTimeout(() => {
                document.getElementById('jitsi-shortcode-copied').classList.remove('fade-in');
            }, 2000);
        }
	});
});

(function ($) {
    $(document).ready(function(){
        $('.jitsi_datepicker').each(function(){
            $(this).datetimepicker({
                format: 'Y-m-d H:i'
            });
        });

        $('.jitsi_timepicker').each(function(){
            $(this).datetimepicker({
                datepicker:false,
                format: 'H:i'
            });
        });

        $('[name^=jitsi_pro__]').on('change', function(){
            var field = $(this).attr('id');            
            $('[data-depend]').each(function(){
                var that = this;
                var depends = $(this).data('depend');
                var depValues = $(this).data('value');
                if(depends.includes(field)){
                    var shouldDisplay = true;
                    $.each(depends, function(index, value){
                        if($('#' + value).is(':checkbox')){
                            var value = $('#' + value).is(':checked') ? 1 : 0;
                        } else {
                            var value = $('#' + value).val();
                        }

                        if(value != depValues[index]){
                            shouldDisplay = false;
                        } 
                    });
                    if(shouldDisplay){
                        $(that).show();
                    } else {
                        $(that).hide();
                    }
                }
            });
        });

        function jitsi_manage_depend(el){
            let sourcedata = el.data('depend');
            let hide = false;
            $.each(sourcedata, function(index, data){
                if($(`[name=${data.field}]:checked`).val() != data.value){
                    hide = true;
                }
            });
            if(hide){
                el.closest('tr').css('display', 'none');
            } else {
                el.closest('tr').css('display', 'table-row');
            }
        }

        $('.jitsi-admin-field[data-depend]').each(function(){
            jitsi_manage_depend($(this));
        });

        $('body').on('change', '.jitsi-admin-field', function(){
            $('.jitsi-admin-field[data-depend]').each(function(){
                jitsi_manage_depend($(this));
            });
        });
    });
}(jQuery));