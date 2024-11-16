/*
 *Ultimate Affiliate Pro - MLM Chart
 */
"use strict";
function uapDrawChart() {
      var rows = [];

      // parent
      var theParentID = jQuery( '.uap-js-mlm-view-affiliate-children-parent-data' ).attr('data-parent_id');
      var parent = jQuery( '.uap-js-mlm-view-affiliate-children-parent-data' ).attr('data-parent');
      var parentAvatar = jQuery( '.uap-js-mlm-view-affiliate-children-parent-data' ).attr('data-parent_avatar');
      var parentFullName = jQuery( '.uap-js-mlm-view-affiliate-children-parent-data' ).attr('data-parent_full_name');
      if ( parent ){
          var htmlData = '<div class="uap-mlm-tree-avatar-child uap-mlm-tree-avatar-parent"><img src="' + parentAvatar + '" /></div><div class="uap-mlm-tree-name-child">'+ parentFullName +'</div>';
          rows.push( [ {v: theParentID, f: htmlData }, '', '' ] );
      }

      // affiliate
      var affiliateId = jQuery( '.uap-js-mlm-view-affiliate-data' ).attr('data-affiliate_id');
      var affiliateAvatar = jQuery( '.uap-js-mlm-view-affiliate-data' ).attr('data-affiliate_avatar');
      var affiliateFullName = jQuery( '.uap-js-mlm-view-affiliate-data' ).attr('data-parent_full_name');
      var htmlData = '<div class="uap-mlm-tree-avatar-child uap-mlm-tree-avatar-main"><img src="' + affiliateAvatar + '" /></div><div class="uap-mlm-tree-name-child">'+ affiliateFullName +'</div>';
      rows.push( [ {v: affiliateId, f: htmlData }, theParentID, 'Main Affiliate' ] );

      // children
      jQuery( '.uap-js-mlm-view-affiliate-children-data' ).each(function( e, html ){
            var affiliateId = jQuery( html ).attr('data-id');
            var avatar = jQuery( html ).attr('data-avatar');
            var name = jQuery( html ).attr('data-full_name');
            var parentId = jQuery( html ).attr('data-parent_id');
            var amount = jQuery( html ).attr('data-amount');
            htmlData = '<div class="uap-mlm-tree-avatar-child"><img src="' + avatar + '" /></div><div class="uap-mlm-tree-name-child">'+ name +'</div>';
            rows.push( [ {v: affiliateId, f: htmlData }, parentId, amount ] );
      })

      var data = new google.visualization.DataTable();
      data.addColumn('string', 'Name');
      data.addColumn('string', 'Manager');
      data.addColumn('string', 'ToolTip');

      data.addRows( rows );
      var chart = new google.visualization.OrgChart(document.getElementById('uap_mlm_chart'));
      if ( parent ){
        data.setRowProperty(0, 'style', 'background-color: #2a81ae; color: #fff;');
        data.setRowProperty(1, 'style', 'background-color: #f25a68; color: #fff;');
      }
      chart.draw(data, {allowHtml:true, size:"medium", allowCollapse:true});
}

window.addEventListener( 'DOMContentLoaded', function(){
    google.charts.load('current', {packages:["orgchart"]});
    google.charts.setOnLoadCallback(uapDrawChart);
});
