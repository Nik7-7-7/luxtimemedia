<?php wp_enqueue_script( 'ulp_printThis' );?>
<span class="ulp-js-init-print-this" data-load_css="<?php echo ULP_URL . 'assets/css/public.css';?>"></span>

<?php if (empty($data ['items'])):?>

    <div class="ulp-additional-message"><?php esc_html_e('You have not received any Certificate yet!', 'ulp');?></div>

<?php else :?>



<div class="ulp-certificate-list-wrapper">

<?php foreach ($data ['items'] as $object):?>

	<div class="ulp-certificate-item">

		<div class="ulp-certificate-item-top">

        	<span onclick="ulpOpenCertificate(<?php echo esc_attr($object->id);?>);">

            	<i class="fa-ulp fa-certificate_grade-ulp" id="wishlist-icon"></i>

            </span>

        </div>

        <div class="ulp-certificate-item-content">

        	<?php echo ulp_print_date_like_wp($object->obtained_date);?>

            <span onclick="ulpOpenCertificate(<?php echo esc_attr($object->id);?>);"><?php echo esc_ulp_content($object->certificate_title);?></span>

        </div>

        <div class="ulp-certificate-item-bottom">

        	<?php echo esc_html($object->course_name);?>

        </div>

    </div>

 <?php endforeach;?>

</div>

  <!--table>

      <tr>

          <td><?php esc_html_e('Certificate Title', 'ulp');?></td>

          <td><?php esc_html_e('Courses', 'ulp');?></td>

          <td><?php esc_html_e('date', 'ulp');?></td>

      <tr/>

      <?php foreach ($data ['items'] as $object):?>

          <tr>

              <td class="ulp-like-link-span ulp-pointer" onclick="ulpOpenCertificate(<?php echo esc_attr($object->id);?>);"><?php echo esc_ulp_content($object->certificate_title);?></td>

              <td><?php echo esc_ulp_content($object->course_name);?></td>

              <td><?php echo ulp_print_date_like_wp($object->obtained_date);?></td>

          </tr>

      <?php endforeach;?>

  </table-->

<?php endif;?>
