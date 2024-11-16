/*
* Ultimate Learning Pro - Questions Functions
*/
"use strict";
var UlpAdminQuestionsAnswers = {

    init: function(args){
        var obj = this;
        obj.setAttributes(obj, args);

        //jQuery(document).on( 'ready', function(){
        document.addEventListener("DOMContentLoaded", function() {
          jQuery('#ulp_answer_type').on('change', function(){
            jQuery('.ulp_div_to_show_hide').css('display', 'none');
            var v = jQuery('#ulp_answer_type').val();
            switch (v){
              case '1':
                var id = '#free_choice_type_of_answer';
                break;
              case '2':
                var id = '#single_choice_type_of_answer';
                break;
              case '3':
                var id = '#multi_choice_type_of_answer';
                break;
              case '4':
                var id = '#true_or_false_type_of_answer';
                break;
              case '5':
                var id = '#essay_type_of_answer';
                break;
              case '6':
                var id = '#sorting_type_of_answer';
                break;
              case '7':
                var id = '#fill_in_type_of_answer';
                break;
              case '8':
                var id = '#image_single_type_of_answer';
                break;
              case '9':
                var id = '#image_multiple_type_of_answer';
                break;
              case '10':
                var id = '#matching_type_of_answer';
                break;
            }
            jQuery(id).css('display', 'block');
          });

          jQuery('#ulp_add_values_for_multiple_answers').on( 'click', function() {
              var str = '<div class="input-group ulp-input-group ulp-the-simple-answers"><input type="text" value="" name="answers_multiple_answers_possible_values[]" class="form-control ulp-form-control" /><span class="ulp-delete-parent ulp-input-group-addon input-group-addon" onClick="ulpRemoveElementFromLeft(this);"><i class="fa-ulp fa-remove-ulp ulp-pointer"></i></span></div>';
              jQuery('#ulpmultipleanswers_wrapp').append(str);
          });

          jQuery('#ulp_add_values_for_single_answers').on( 'click', function() {
              var str = '<div class="input-group ulp-input-group ulp-the-simple-answers"><input type="text" value="" name="answers_single_answer_possible_values[]" class="form-control ulp-form-control" /><span class="ulp-delete-parent  ulp-input-group-addon input-group-addon" onClick="ulpRemoveElementFromLeft(this);"><i class="fa-ulp fa-remove-ulp ulp-pointer"></i></span></div>';
              jQuery('#ulpsingleanswers_wrapp').append(str);
          });

          /// choose image - single choice
          jQuery('#ulp_add_values_for_image_single_answers').on( 'click', function() {
              var last_num = jQuery('#ulp_image_single_answers_wrapp .ulp_answer_num').last().html();
              last_num++;
              var str = '<div class="input-group ulp-input-group ulp-the-image-answers"><span class="input-group-addon ulp-input-group-addon" id="basic-addon1">ID:<span class="ulp_answer_num">' + last_num + '</span></span><input type="text" placeholder="...upload an image from Media Library" value="" onClick="openMediaUp(this);" name="image_answers_single_answer_possible_values[]" class="form-control ulp-form-control" /><span class="ulp-delete-parent input-group-addon ulp-input-group-addon" onClick="ulpRemoveElementFromLeft(this);ulpUpdateNumOfEachDiv(\'#ulp_image_single_answers_wrapp .ulp_answer_num\');"><i class="fa-ulp fa-remove-ulp ulp-pointer"></i></span></div>';
              jQuery('#ulp_image_single_answers_wrapp').append(str);
              ulpUpdateNumOfEachDiv('#ulp_image_single_answers_wrapp .ulp_answer_num');
          });

          /// choose image - multiple choice
          jQuery('#ulp_add_values_for_image_multiple_answers').on( 'click', function() {
              var last_num = jQuery('#ulp_image_multiple_answers_wrapp .ulp_answer_num').last().html();
              last_num++;
              var str = '<div class="input-group ulp-input-group ulp-the-multiple-answers"><span class="input-group-addon ulp-input-group-addon" id="basic-addon1">ID:<span class="ulp_answer_num">' + last_num + '</span></span><input type="text" placeholder="...upload an image from Media Library" value="" onClick="openMediaUp(this);" name="image_answers_multiple_answers_possible_values[]" class="form-control ulp-form-control" /><span class="ulp-delete-parent input-group-addon ulp-input-group-addon" onClick="ulpRemoveElementFromLeft(this);ulpUpdateNumOfEachDiv(\'#ulp_image_multiple_answers_wrapp .ulp_answer_num\');"><i class="fa-ulp fa-remove-ulp ulp-pointer"></i></span></div>';
              jQuery('#ulp_image_multiple_answers_wrapp').append(str);
              ulpUpdateNumOfEachDiv('#ulp_image_multiple_answers_wrapp .ulp_answer_num');
          });

          ///matching
          jQuery('#ulp_add_values_for_matching').on( 'click', function() {
            var str = '<div class="input-group ulp-input-group ulp-the-matching-answers">'
                      + '<input type="text" class="form-control ulp-form-control" value="" name="matching_micro_questions[]" placeholder="Question" />'
                      + '<input type="text" class="form-control ulp-form-control ulp-the-matching-answers-single" value="" name="matching_micro_questions_answers[]" placeholder="Answer" />'
                      + '<span class="ulp-delete-parent input-group-addon ulp-input-group-addon" onClick="ulpRemoveElementFromLeft(this);">'
                      + '<i class="fa-ulp fa-remove-ulp ulp-pointer"></i></span></div>';
            jQuery('#ulp_matching_qanda_wrapp').append(str);
          });

          jQuery('#ulp_add_values_for_sorting_type').on( 'click', function() {
              var str = '<div class="input-group ulp-input-group ulp-the-sorting-answers ulp-the-sorting-answers-st"><span class="input-group-addon ulp-input-group-addon"><i class="fa-ulp fa-sort-ulp"></i></span><input type="text" value="" name="answers_sorting_type[]" class="form-control ulp-form-control" /><span class="ulp-delete-parent input-group-addon ulp-input-group-addon" onClick="ulpRemoveElementFromLeft(this);"><i class="fa-ulp fa-remove-ulp ulp-pointer"></i></span></div>';
              jQuery('#ulpsrotingtype_wrapp').append(str);
          });
        });
    },

    setAttributes: function(obj, args){
        for (var key in args) {
          obj[key] = args[key];
        }
    },

}


UlpAdminQuestionsAnswers.init();
