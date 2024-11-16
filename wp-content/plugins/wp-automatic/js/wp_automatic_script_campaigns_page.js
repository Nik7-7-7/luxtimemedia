jQuery(document).ready(function ($) {
  $(
    '<a id="wp_automatic_btn_import" href="test" class="page-title-action">Import</a>'
  ).insertAfter(".page-title-action");


  //click handler for the import button to insert the upload form HTML if not available already and show it if hidden already 
    $("#wp_automatic_btn_import").click(function (e) {

    //prevent the default action
        e.preventDefault();

    //if the form is not available, insert it 
    const formHtml = `<div class="upload-plugin-wrap">
    <div class="upload-plugin">
<p class="install-help">If you have a previous export in .json format, you may import it by uploading it here.</p>
<form method="post" enctype="multipart/form-data" class="wp-upload-form" >
    <label class="screen-reader-text" for="pluginzip">Plugin zip file</label>
    <input type="file" id="pluginzip" name="wp_automatic_upload_file" accept=".json">
    <input type="submit" name="wp_automatic_upload_file" id="install-plugin-submit" class="button" value="Import Now" >	</form>
</div>
    </div>`;

    if ($(".upload-plugin-wrap").length == 0) {
       
       //insert the form HTML after hr.wp-header-end
        $("hr.wp-header-end").after(formHtml);
       
    }


    // toggle display for div wih class upload-plugin
    $(".upload-plugin").toggle();



});

});