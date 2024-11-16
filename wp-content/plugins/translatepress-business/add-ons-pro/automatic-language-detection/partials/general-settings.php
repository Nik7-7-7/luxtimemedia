<tr> <td><h4 style="color: black"><?php esc_html_e( 'Automatic User Language Detection', 'translatepress-multilingual' ); ?></h4> </td>
    <td>
            <?php echo wp_kses( sprintf(__( 'Go to <a href="%s" target="_self">Advanced</a> tab to change this feature\'s settings', 'translatepress-multilingual' ), esc_url(admin_url('admin.php?page=trp_advanced_page#automatic_user_language_detection'))), array('a' => array('href' => array(), 'target' =>array(), 'title' => array()), 'br' => array()) ); ?>
    </td>
</tr>