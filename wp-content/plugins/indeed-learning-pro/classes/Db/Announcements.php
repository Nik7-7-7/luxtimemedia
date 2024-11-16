<?php
namespace Indeed\Ulp\Db;
if (!defined('ABSPATH')){
   exit();
}

class Announcements
{
    private $metaKeyForCourse = 'ulp_course_id';

    public function __construct(){}

    public function countAllByCourse($courseId=0, $status='')
    {
        global $wpdb;
        if (empty($courseId)){
            return 0;
        }

        $query = $wpdb->prepare( "SELECT COUNT(b.post_id) FROM {$wpdb->postmeta} b
                                      INNER JOIN {$wpdb->posts} a
                                      ON a.ID=b.post_id
                                      WHERE
                          					  a.post_type='ulp_announcement'
                          					  AND
                                      b.meta_key=%s
                                      AND
                                      b.meta_value=%d
        ", $this->metaKeyForCourse, $courseId );

        if ($status){
            $query .= " AND a.post_status=%s ";
            $query = $wpdb->prepare($query, $status);
        }
        $result = $wpdb->get_var($query);
        if ($result){
            return $result;
        }
        return 0;
    }

    public function saveToCourse($announcementId=0, $courseId=0)
    {
        global $wpdb;
        if (empty($announcementId) || empty($courseId)){
            return false;
        }
        update_post_meta($announcementId, $this->metaKeyForCourse, $courseId);
    }

    public function deleteFromCourse($announcementId=0)
    {
        global $wpdb;
        if (empty($announcementId)){
            return false;
        }
        delete_post_meta($announcementId, $this->metaKeyForCourse);
    }

    public function get($announcementId=0)
    {
        global $wpdb;
        if (empty($announcementId)){
            return false;
        }
        $query = $wpdb->prepare("SELECT ID,post_author,post_date,post_date_gmt,
                                        post_content,post_title,post_excerpt,post_status,comment_status,
                                        ping_status,post_password,post_name,to_ping,pinged,post_modified,
                                        post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count
                                      FROM {$wpdb->posts} WHERE ID=%d", $announcementId);
        $result = $wpdb->get_row($query);
        return $result;
    }

    public function getCourseIdByAnnouncement($announcementId=0)
    {
        if (empty($announcementId)){
            return 0;
        }
        return get_post_meta($announcementId, $this->metaKeyForCourse, true);
    }

    public function getAllForCourse($courseId=0, $limit=30, $offset=0)
    {
        global $wpdb;
        if (empty($courseId)){
            return false;
        }
        $query = "SELECT a.ID,a.post_author,a.post_date,a.post_date_gmt,a.post_content,a.post_title,a.post_excerpt,a.post_status,
                         a.comment_status,a.ping_status,a.post_password,a.post_name,a.to_ping,a.pinged,a.post_modified,a.post_modified_gmt,
                         a.post_content_filtered,a.post_parent,a.guid,a.menu_order,a.post_type,a.post_mime_type,a.comment_count
                      FROM {$wpdb->posts} a
                      INNER JOIN {$wpdb->postmeta} b
                      ON a.ID=b.post_id
                      WHERE
                      b.meta_key=%s
                      AND
                      b.meta_value=%d
                      AND
                      a.post_status='publish'
                      ORDER BY a.ID DESC ";
        $query = $wpdb->prepare( $query, $this->metaKeyForCourse, $courseId );
        if ($limit){
        		$limit = sanitize_text_field($limit);
        		$offset = sanitize_text_field($offset);
        		$query .= $wpdb->prepare( " LIMIT %d OFFSET %d ", $limit, $offset );
        }
        $result = $wpdb->get_results($query);
        if ($result){
            return $result;
        }
        return false;
    }


}
