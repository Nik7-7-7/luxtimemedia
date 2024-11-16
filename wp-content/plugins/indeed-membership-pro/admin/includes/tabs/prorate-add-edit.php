<?php
$groupId = isset( $_GET['id'] ) ? sanitize_text_field($_GET['id']) : false;
$groupData = \Indeed\Ihc\Db\ProrateMembershipGroups::getOne( $groupId );
$memberships = \Indeed\Ihc\Db\Memberships::getAll();
$i = 1;
?>
<form method="post" action="<?php echo admin_url( 'admin.php?page=ihc_manage&tab=prorate_subscription' );?>" >
	<input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />
	<?php if ( $groupId !== false ):?>
			<input type="hidden" name="id" value="<?php echo esc_attr($groupId);?>" />
	<?php endif;?>
	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('Prorating Subscription - Prorate Groups', 'ihc');?></h3>
		<div class="inside">
      <div class="iump-form-line">
				<div class="row">
					<div class="col-xs-6">
						 <div class="input-group">
							<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Group Name', 'ihc');?></span>
							<input type="text" name="name" value="<?php if ( isset( $groupData['name'] ) ) echo esc_attr($groupData['name']);?>" class="form-control" />
							</div>
					</div>
				</div>
			</div>

      <div class="iump-form-line">
          <h2><?php esc_html_e( 'Memberships on this Prorate Group', 'ihc' );?></h2>
					<p><?php esc_html_e( 'Add/Remove Memberships from this Group and place them on the right order for Upgrade/Downgrade algorithm.', 'ihc' );?></p>
      </div>
			<div class="iump-form-line">
	      <div class="ihc-prorate-memberships-groups" data-count="<?php echo esc_attr($i);?>" >
	          <?php if ( $groupData ):?>
	              <?php foreach ( $groupData['memberships'] as $membershipId ):?>
										<div class="ihc-sortable-item ihc-prorate-group-item" id="<?php echo esc_attr('ihc_prorate_membership_no_' . $i);?>">
			                  <label class="ihc-prorate-memberhip-item-no"><?php echo esc_html($i) . '.';?></label>
			                  <select class="ihc-prorate-memberhip-item-select" name="memberships[]">
			                    <option></option>
			                    <?php foreach ( $memberships as $membershipData ): ?>
			                      <option value="<?php echo esc_attr($membershipData['id']);?>"
																	<?php echo ($membershipId === $membershipData['id']) ? 'selected' : '';?> ><?php
																echo esc_html($membershipData['label']);
														?></option>
			                    <?php endforeach;?>
			                  </select>
												<span class="ihc-js-prorate-remove-entry" ><i class="fa-ihc fa-remove-ihc"></i></span>
			              </div>
	                  <?php $i++;?>
	              <?php endforeach;?>
	          <?php else : ?>
	              <div class="ihc-sortable-item ihc-prorate-group-item" id="<?php echo esc_attr('ihc_prorate_membership_no_1');?>">
	                  <label class="ihc-prorate-memberhip-item-no"><?php echo esc_html($i) . '.';?></label>
	                  <select class="ihc-prorate-memberhip-item-select" name="memberships[]">
	                    <option></option>
	                    <?php foreach ( $memberships as $membershipData ): ?>
	                      <option value="<?php echo esc_attr($membershipData['id']);?>"><?php echo esc_html($membershipData['label']);?></option>
	                    <?php endforeach;?>
	                  </select>
										<span class="ihc-js-prorate-remove-entry"><i class="fa-ihc fa-remove-ihc"></i></span>
	              </div>
	          <?php endif;?>
	      </div>
			</div>
			<div class="iump-form-line">
				<p><?php esc_html_e( 'If a Membership already belongs to another Prorate Group assigning to this one will be removed from the other.', 'ihc' );?></p>
	      <div class="ihc-bttn-wrapp ihc-prorate-group-add-new">
	        <div class="ihc-js-prorate-add-new-level-to-group-bttn indeed-add-new-like-wp">
						<i class="fa-ihc fa-add-ihc"></i>
						<?php
	          esc_html_e( 'Add New Membership', 'ihc' );
	        ?></div>
	      </div>
			</div>
      <div class="ihc-wrapp-submit-bttn ihc-submit-form">
        <input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save_prorate" class="button button-primary button-large" />
      </div>

    </div>
  </div>
</form>

<!-- used in js -->
<div class="ihc-display-none ihc-js-memberships-hidden" >
  <div class="ihc-sortable-item ihc-prorate-group-item" id="<?php echo esc_attr('ihc_prorate_membership_no_1');?>">
      <label class="ihc-prorate-memberhip-item-no"></label>
      <select class="ihc-prorate-memberhip-item-select" name="memberships[]">
        <option></option>
        <?php foreach ( $memberships as $membershipData ): ?>
          <option value="<?php echo esc_attr($membershipData['id']);?>"><?php echo esc_html($membershipData['label']);?></option>
        <?php endforeach;?>
      </select>
			<span class="ihc-js-prorate-remove-entry"><i class="fa-ihc fa-remove-ihc"></i></span>
  </div>
</div>
<!-- end of used in js -->
