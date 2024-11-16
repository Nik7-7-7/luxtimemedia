<?php if ( $hideForm ):?>
  <div class="ihc-wrapp-the-errors"><?php echo esc_ump_content($hideFormMessage);?></div>
<?php elseif ( $success ):?>
  <div class="ihc-login-success"><?php echo esc_ump_content($successMessage);?></div>
<?php elseif ( $userType === 'unreg' ): ?>

  <!-- Login Form - Start -->
  <div class="<?php echo esc_attr( $wrappClass );?> <?php echo esc_attr($template);?>">
      <form method="post" id="<?php echo esc_attr($formId);?>">
          <input type="hidden" name="ihcaction" value="<?php echo esc_attr($ihcAction);?>" />
          <input type="hidden" name="<?php echo esc_attr($nonceName);?>" value="<?php echo esc_attr($nonce);?>" />

          <?php if ( !empty( $isLocker ) ):?>
              <input type="hidden" name="locker" value="1" />
          <?php endif;?>

          <?php if ( $usernameField ):?>
          <!-- Username field -->
          <div class="impu-form-line-fr">
              <input type="text" value="" name="log" id="iump_login_username" placeholder="<?php echo esc_html__('Username:', 'ihc');?>" />
          </div>
          <!-- End of username field -->
          <?php endif;?>

          <?php if ( $passwordField ):?>
          <!-- Password field -->
          <div class="impu-form-line-fr">
              <input type="password" value="" name="pwd" id="iump_login_password" placeholder="<?php echo esc_html__('Password:', 'ihc');?>" />
              <span  class="ihc-hide-login-pw hide-if-no-js" data-toggle="0" aria-label="Show password">
              <span class="dashicons dashicons-visibility" aria-hidden="true"></span>
                </span>
          </div>
          <!-- End of Password field -->
          <?php endif;?>

          <?php if ( $emailLostPasswordField ):?>
              <div class="impu-form-line-fr">
                 <input type="text" value="" name="email_or_userlogin" placeholder="<?php echo esc_html__('Username or E-mail', 'ihc');?>" />
              </div>
          <?php endif;?>

          <!-- captcha -->
          <?php if ( !empty($captcha['show_captcha']) && !empty( $captcha['html'] ) ): ?>
              <?php echo esc_ump_content($captcha['html']);?>
          <?php endif;?>
          <!-- end of captcha -->

          <!-- Submit bttn -->
          <?php if ( $ihcAction === 'reset_pass' ):?>
              <div class="impu-form-submit">
                  <input type="submit" value="<?php echo esc_attr( $submitValue );?>" name="Submit" <?php echo esc_ump_content($disabledSubmit);?> class="button button-primary button-large"/>
              </div>
          <?php else:?>
              <div class="impu-form-line-fr impu-form-submit">
                  <input type="submit" value="<?php echo esc_attr( $submitValue );?>" name="Submit" <?php echo esc_ump_content($disabledSubmit);?> class="button button-primary button-large"/>
              </div>
          <?php endif;?>
          <!-- End of Submit bttn -->

          <!-- Social -->
          <?php if ( $social ):?>
              <?php echo esc_ump_content($social);?>
          <?php endif;?>
          <!-- End of Social -->

          <div class="impu-temp3-bottom">
                <!-- Remember me checkbox -->
                <?php if ( !empty( $settings['ihc_login_remember_me'] ) ):?>
                <div class="impu-remember-wrapper">
                    <input type="checkbox" value="forever" name="rememberme" class="impu-form-input-remember" />
                    <span class="impu-form-label-fr impu-form-label-remember"><?php echo esc_html__('Remember Me');?></span>
                </div>
                <?php endif;?>
                <!-- End of remember me --->

                <!-- Additional links -->
                <?php if ( !empty( $settings['ihc_login_register'] ) || !empty( $settings['ihc_login_pass_lost'] ) ):?>
                      <div class="impu-form-links">
                          <?php if ( !empty( $settings['ihc_login_register'] ) ):?>
                              <div class="impu-form-links-reg"><a href="<?php echo esc_url($registerPageUrl);?>"><?php echo esc_html__('Register', 'ihc');?></a></div>
                          <?php endif;?>
                          <?php if ( !empty( $settings['ihc_login_pass_lost'] ) ):?>
                              <div class="impu-form-links-pass"><a href="<?php echo esc_url($lostPassUrl);?>"><?php echo esc_html__('Lost your password?', 'ihc');?></a></div>
                          <?php endif;?>
                      </div>
                <?php endif;?>
                <!-- End of Aditional links -->
                <div class="iump-clear"></div>
          </div>

      </form>
  </div>

  <?php if ( $errorCode && $errorMessage ):?>
      <!-- Errors -->
      <?php if ( $ihcAction === 'reset_pass' ):?>
          <div class="ihc-wrapp-the-errors"><?php echo esc_ump_content($errorMessage);?></div>
      <?php else :?>
          <div class="ihc-login-error-wrapper"><div class="ihc-login-error"><?php echo esc_ump_content($errorMessage);?></div></div>
      <?php endif;?>
      <!-- Errors -->
  <?php endif;?>

  <span class='ihc-js-login-data'	data-user_field='#iump_login_username' data-password_field='#iump_login_password' data-error_message='<?php echo esc_ump_content($ajaxErrorMessage);?>' ></span>
  <!-- End of Login Form -->

<?php elseif ( $userType === 'admin' ) :?>
  <div class="ihc-warning-message">
      <?php echo esc_html__('Administrator Info: Login Form is not showing up once you\'re logged. You may check how it it looks for testing purpose by openeing the page into a separate incognito browser window. ', 'ihc');?>
      <i><?php echo esc_html__('This message will not be visible for other users', 'ihc');?></i>
  </div>
<?php endif;?>
