

<div class="ulp-wrapper">
  <div class="ulp-page-title"><?php echo esc_html($data['username']) .' - <span>'. esc_html__(' Activity', 'ulp').'</span>';?></div>
  <div class="inside">
    <?php if (count($data['userdata'])==0):?>
      <?php esc_html_e('No Activity yet!', 'ulp');?>
    <?php else:?>
    <table class="ulp-admin-tables ">
      <thead class="thead-inverse">
        <tr>
          <th><?php esc_html_e('Entity', 'ulp');?></th>
          <th><?php esc_html_e('Action', 'ulp');?></th>
          <th><?php esc_html_e('Description', 'ulp');?></th>
          <th><?php esc_html_e('Date', 'ulp');?></th>
        </tr>
      </thead>
      <tfoot class="thead-inverse">
        <tr>
          <th><?php esc_html_e('Entity', 'ulp');?></th>
          <th><?php esc_html_e('Action', 'ulp');?></th>
          <th><?php esc_html_e('Description', 'ulp');?></th>
          <th><?php esc_html_e('Date', 'ulp');?></th>
        </tr>
      </tfoot>
      <tbody>
        <?php foreach ($data['userdata'] as $database_entry):?>
          <tr>
              <td><?php echo DbUlp::getPostTitleByPostId($database_entry->entity_id);?></td>
              <td><?php
                  if (isset($data['possible_actions'][$database_entry->action])){
                     echo esc_html($data['possible_actions'][$database_entry->action]);
                  }else{
                     echo esc_html($database_entry->action);
                  }?>
              </td>
              <td><?php echo esc_html($database_entry->description);?></td>
              <td><?php echo ulp_print_date_like_wp($database_entry->event_time);?></td>
          </tr>
        <?php endforeach;?>
      </tbody>
    </table>

    <div class="ulp-clear"></div>
    <?php if (!empty($data['pagination'])):?>
        <?php echo esc_ulp_content($data['pagination']);?>
    <?php endif;?>

    <?php endif;?>
  </div>
</div>
