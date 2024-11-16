<style type="text/css" media="print">#uap_info_affiliate_bar { display:none; }</style>
<?php if ( is_admin_bar_showing() ):?>
	<style type="text/css" media="screen" id="uap_iab_style" >
		html { margin-top: 72px !important; }
		* html body { margin-top: 72px !important; }
		.uap-info-affiliate-bar-wrapper{
			top:32px;
		}
		@media screen and ( max-width: 782px ) {
			html { margin-top: 98px !important; }
			* html body { margin-top: 98px !important; }
			.uap-info-affiliate-bar-wrapper{
			top:46px;
			}
		}
		
	</style>
<?php else :?>
<style type="text/css" media="screen" id="uap_iab_style">
	html { margin-top: 40px !important; }
	* html body { margin-top: 40px !important; }
	@media screen and ( max-width: 782px ) {
		html { margin-top: 52px !important; }
		* html body { margin-top: 52px !important; }
	}
</style>
<?php endif;?>
