<?php
namespace Indeed\Ulp\Db;
if (!defined('ABSPATH')){
   exit();
}

class Comments
{

    public function __construct(){}

    public function countForPost($postId=0)
    {
        global $wpdb;
        if (!$postId){
            return false;
        }
        $query = $wpdb->prepare("SELECT COUNT(comment_ID) FROM {$wpdb->comments} WHERE comment_post_ID=%d", $postId);
        return $wpdb->get_var($query);
    }

    public function getForPost($postId=0, $limit=30, $offset=0)
    {
        global $wpdb;
        if (!$postId){
            return false;
        }
        $query = $wpdb->prepare("
                    SELECT comment_ID,comment_post_ID,comment_author,comment_author_email,comment_author_url,comment_author_IP,comment_date,comment_date_gmt,comment_content,comment_karma,comment_approved,comment_agent,comment_type,comment_parent,user_id FROM {$wpdb->comments}
                        WHERE
                        comment_post_ID=%d
                        LIMIT %d OFFSET %d
        ", $postId, $limit, $offset);
        return $wpdb->get_results($query);
    }

}
