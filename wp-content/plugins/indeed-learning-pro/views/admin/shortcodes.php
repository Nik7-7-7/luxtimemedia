<div class="ulp-stuffbox">
		<h3 class="ulp-h3"><?php esc_html_e('All Shortcodes', 'ulp');?></h3>
		<div class="inside">
      <table class="table ulp-custom-table ulp-shortcodes-table">
            <thead class="thead-inverse">
              <tr>
                <th><?php esc_html_e('Shortcode', 'ulp');?></th>
                <th><?php esc_html_e('What it does', 'ulp');?></th>
                <th><?php esc_html_e('Arguments available', 'ulp');?></th>
              </tr>
            </thead>
            <tbody>
							<?php $i = 0;?>
              <?php foreach ($data['available_shortcodes'] as $shortcode => $array):?>
            			<tr>
                          <td><?php echo esc_ulp_content('[' . $shortcode . ']');?></td>
                          <td><?php echo esc_html($array['what_can_do']);?></td>
                          <td><?php echo (isset($array['args'])) ? $array['args'] : '-';?></td>
            			</tr>
              <?php endforeach;?>
            </tbody>
          </table>
		</div>
	</div>
