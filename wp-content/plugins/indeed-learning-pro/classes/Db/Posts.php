<?php
namespace Indeed\Ulp\Db;
if (!defined('ABSPATH')){
   exit();
}

class Posts
{

    public function __construct(){}

    public function save($postData=[])
    {
        $postId = isset($postData['ID']) ? $postData['ID'] : 0;
        $defaults = $this->get($postId, true);
        if ( $defaults ){
            foreach ($defaults as $key => $value){
                if (!isset($postData[$key])){
                    $postData[$key] = $value;
                }
            }
        }
        if (empty($postData['ID'])){

            if (empty($postData['post_title'])){
                $postData['post_title'] = $postData['ID'];

            }
            $postData['post_name'] = wp_unique_post_slug( $postData['post_title'], 0, 'publish', $postData['post_type'], 0 );
            return wp_insert_post($postData);
        }
        return wp_update_post($postData);
    }

    public function get($postId=0, $defaultValue=false)
    {
        global $wpdb;
        if (!$postId && !$defaultValue){
            return false;
        }
        if (!$postId && $defaultValue){
            return [

                    'post_author'								=> '',
                    'post_date'									=> gmdate('Y-m-d H:i:59'),
                    'post_date_gmt'							=> gmdate('Y-m-d H:i:59'),
                    'post_content'							=> '',
                    'post_title'								=> '',
                    'post_excerpt'							=> '',
                    'post_status'								=> 'pending',
                    'comment_status'						=> 'open',
                    'ping_status'								=> 'open',
                    'post_password'							=> '',
                    'post_name'								  => '',
                    'to_ping'								    => '',
                    'pinged'								    => '',
                    'post_modified'							=> gmdate('Y-m-d H:i:59'),
                    'post_modified_gmt'					=> gmdate('Y-m-d H:i:59'),
                    'post_content_filtered'			=> '',
                    'post_parent'								=> 0,
                    'guid'											=> '', /// link
                    'menu_order'								=> 0,
                    'post_type'									=> 'post',
                    'post_mime_type'						=> '',
                    'comment_count'							=> 0,
            ];
        }
        $query = $wpdb->prepare("SELECT ID,post_author,post_date,post_date_gmt,post_content,post_title,post_excerpt,post_status,comment_status,ping_status,post_password,post_name,to_ping,pinged,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count
                                    FROM {$wpdb->posts} WHERE ID=%d;", $postId);
        $data = $wpdb->get_row($query);
        if ($data){
            return (array)$data;
        } else {
            return false;
        }
    }

    public function select($postType='post', $limit=30, $offset=0, $postAuthor=0, $onlyCount=false)
    {
        global $wpdb;
        if ($offset<0 || $limit<0){
          return [];
        }

        $query = $wpdb->prepare("
              WHERE
              post_type=%s
              AND
              post_author=%s
              AND
              post_status NOT IN ('trash', 'auto-draft')
        ", $postType, $postAuthor);
        if ($onlyCount){
            $query = "SELECT COUNT(ID) FROM {$wpdb->posts} " . $query;
            return $wpdb->get_var($query);
        } else {
            $query = "SELECT ID,post_author,post_date,post_date_gmt,post_content,post_title,post_excerpt,post_status,comment_status,ping_status,post_password,post_name,to_ping,pinged,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count FROM {$wpdb->posts} " . $query;
            $query .= " ORDER BY ID DESC ";
            $query .= $wpdb->prepare("LIMIT %d OFFSET %d", $limit, $offset);
        }
        $data = $wpdb->get_results($query);
        return $data;
    }

    public function selectByMetaValue($postType='post', $limit=30, $offset=0, $metaKey='', $metaValue='', $onlyCount=false)
    {
        global $wpdb;
        if ($onlyCount){
            $query = $wpdb->prepare(
              "
                  SELECT COUNT(a.ID) as c FROM {$wpdb->posts} a
                          INNER JOIN {$wpdb->postmeta} b
                          ON a.ID=b.post_id
                          WHERE
                          b.meta_key=%s
                          AND
                          b.meta_value=%s
                          AND
                          a.post_type=%s
              ", $metaKey, $metaValue, $postType
            );
            return $wpdb->get_var($query);
        } else {
          $query = $wpdb->prepare(
            "
                SELECT a.ID,a.post_author,a.post_date,a.post_date_gmt,a.post_content,a.post_title,a.post_excerpt,a.post_status,a.comment_status,a.ping_status,a.post_password,a.post_name,a.to_ping,a.pinged,a.post_modified,a.post_modified_gmt,a.post_content_filtered,a.post_parent,a.guid,a.menu_order,a.post_type,a.post_mime_type,a.comment_count
                        FROM {$wpdb->posts} a
                        INNER JOIN {$wpdb->postmeta} b
                        ON a.ID=b.post_id
                        WHERE
                        b.meta_key=%s
                        AND
                        b.meta_value=%s
                        AND
                        a.post_type=%s
                        ORDER BY a.ID DESC
                        LIMIT %d OFFSET %d
            ", $metaKey, $metaValue, $postType, $limit, $offset
          );
          return $wpdb->get_results($query);
        }
        return false;
    }

    public function getByAuthor($postType='post', $limit=30, $offset=0, $onlyCount=false, $postAuthor=0)
    {
        global $wpdb;
        if ($onlyCount){
            $query = $wpdb->prepare(
              "
                  SELECT COUNT(ID) as c FROM {$wpdb->posts}
                          WHERE
                          post_type=%s
                          AND
                          post_author=%d
              ", $postType, $postAuthor
            );
            return $wpdb->get_var($query);
        } else {
          $query = $wpdb->prepare(
            "
                SELECT ID,post_author,post_date,post_date_gmt,post_content,post_title,post_excerpt,post_status,comment_status,ping_status,post_password,post_name,to_ping,pinged,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count
                        FROM {$wpdb->posts}
                        WHERE
                        post_type=%s
                        AND
                        post_author=%d
                        ORDER BY ID DESC
                        LIMIT %d OFFSET %d
            ", $postType, $postAuthor, $limit, $offset
          );
          return $wpdb->get_results($query);
        }
        return false;
    }


}
