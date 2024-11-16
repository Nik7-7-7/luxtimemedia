<?php
namespace Indeed\Ulp\Db;
if (!defined('ABSPATH')){
   exit();
}

class QandA
{
  private $metaKeyForCourse = 'ulp_qanda_course_id';

  public function __construct(){}

  public function countAllByInstructor($uid=0)
  {
      global $wpdb;
      if (!$uid){
          return 0;
      }
      $courses = \DbUlp::allCoursesIdsForInstructor($uid);
      $coursesIds = implode(',', $courses);
      $query = "
              SELECT COUNT(a.ID) FROM
                  {$wpdb->postmeta} b
                  INNER JOIN {$wpdb->posts} a
                  ON a.ID=b.post_id
                  WHERE
                  a.post_type='ulp_qanda'
                  AND
                  b.meta_key='ulp_qanda_course_id'
                  AND
                  b.meta_value IN ($coursesIds)
      ";
      return $wpdb->get_var($query);
  }

  public function getByInstructor($uid=0, $limit=30, $offset=0)
  {
      global $wpdb;
      if (!$uid){
          return 0;
      }
      $courses = \DbUlp::allCoursesIdsForInstructor($uid);
      $coursesIds = implode(',', $courses);
      $query = $wpdb->prepare("
              SELECT a.ID,a.post_author,a.post_date,a.post_date_gmt,a.post_content,a.post_title,a.post_excerpt,a.post_status,a.comment_status,a.ping_status,a.post_password,a.post_name,a.to_ping,a.pinged,a.post_modified,a.post_modified_gmt,a.post_content_filtered,a.post_parent,a.guid,a.menu_order,a.post_type,a.post_mime_type,a.comment_count,b.meta_id,b.post_id,b.meta_key,b.meta_value
                  FROM
                  {$wpdb->postmeta} b
                  INNER JOIN {$wpdb->posts} a
                  ON a.ID=b.post_id
                  WHERE
                  a.post_type='ulp_qanda'
                  AND
                  b.meta_key='ulp_qanda_course_id'
                  AND
                  b.meta_value IN ($coursesIds)
                  ORDER BY a.ID DESC
                  LIMIT %d OFFSET %d
      ", $limit, $offset);
      return $wpdb->get_results($query);
  }

  public function countAllByCourse($courseId=0, $status='')
  {
      global $wpdb;
      if (empty($courseId)){
          return 0;
      }
      $query = "SELECT COUNT(a.ID) FROM
                    {$wpdb->postmeta} b
                    INNER JOIN {$wpdb->posts} a
                    ON a.ID=b.post_id
                    WHERE
					a.post_type='ulp_qanda'
					AND
                    b.meta_key='ulp_qanda_course_id'
                    AND
                    b.meta_value=%d
      ";
      $query = $wpdb->prepare($query, $courseId);
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

  public function saveToCourse($qandaId=0, $courseId=0)
  {
      global $wpdb;
      if (empty($qandaId) || empty($courseId)){
          return false;
      }
      $save = $wpdb->prepare( "INSERT INTO {$wpdb->postmeta} VALUES( null, %d, %s, %d );", $qandaId, $this->metaKeyForCourse, $courseId );
      return $wpdb->query( $save );
  }

  public function deleteFromCourse($qandaId=0)
  {
      global $wpdb;
      if (empty($qandaId)){
          return false;
      }
      delete_post_meta($qandaId, $this->metaKeyForCourse);
  }

  public function get($qandaId=0)
  {
      global $wpdb;
      if (empty($qandaId)){
          return false;
      }
      $query = $wpdb->prepare("SELECT `ID`,`post_author`,`post_date`,`post_date_gmt`,`post_content`,`post_title`,`post_excerpt`,`post_status`,`comment_status`,`ping_status`,`post_password`,`post_name`,`to_ping`,`pinged`,`post_modified`,`post_modified_gmt`,`post_content_filtered`,`post_parent`,`guid`,`menu_order`,
                                  `post_type`,`post_mime_type`,`comment_count`
                                  FROM {$wpdb->posts} WHERE ID=%d", $qandaId);
      $result = $wpdb->get_row($query);
      return $result;
  }

  public function getCourseIdByQanda($qandaId=0)
  {
      if (empty($qandaId)){
          return 0;
      }
      return get_post_meta($qandaId, $this->metaKeyForCourse, true);
  }

  public function getAllForCourse($courseId=0, $limit=30, $offset=0, $postSearchLike='')
  {
      global $wpdb;
      if (empty($courseId)){
          return false;
      }
      $query = "SELECT a.ID,a.post_author,a.post_date,a.post_date_gmt,a.post_content,a.post_title,a.post_excerpt,a.post_status,a.comment_status,a.ping_status,a.post_password,a.post_name,a.to_ping,a.pinged,a.post_modified,a.post_modified_gmt,a.post_content_filtered,a.post_parent,a.guid,a.menu_order,a.post_type,a.post_mime_type,a.comment_count
                    FROM {$wpdb->posts} a
                    INNER JOIN {$wpdb->postmeta} b
                    ON a.ID=b.post_id
                    WHERE
                    b.meta_key=%s
                    AND
                    b.meta_value=%d
                    AND
                    a.post_status='publish'
      ";
      if ($postSearchLike){
          $query .= " AND (a.post_title LIKE '%$postSearchLike%' OR a.post_content LIKE '%$postSearchLike%') ";
      }
      $query .= " ORDER BY a.ID DESC ";
      if ($limit){
          $limit = sanitize_text_field($limit);
          $offset = sanitize_text_field($offset);
          $query .= $wpdb->prepare( " LIMIT %d OFFSET %d ", $limit, $offset );
      }
      $query = $wpdb->prepare($query, $this->metaKeyForCourse, $courseId);
      $result = $wpdb->get_results($query);
      if ($result){
          return $result;
      }
      return false;
  }

  public function saveQuestion($uid=0, $courseId=0, $title='', $content='')
  {
      if (empty($uid) || empty($courseId)){
          return false;
      }
      $postId = $this->insertPost([
        'post_author'     => $uid,
        'post_title'      => sanitize_textarea_field($title),
        'post_content'    => sanitize_textarea_field($content),
        'post_status'     => 'publish',
        'post_type'       => 'ulp_qanda',
      ]);

      if ($postId){
          $saved = $this->saveToCourse($postId, $courseId);
      }
      if (!empty($saved)){
          return $postId;
      }
      return false;
  }

  public function insertPost($postData=[])
  {
    global $wpdb;
    if (empty($postData)){
        return 0;
    }

    $postName = \DbUlp::createPostName($postData['post_title']);

    $data = [
              'post_author'								=> $postData['post_author'],
              'post_date'									=> gmdate('Y-m-d H:i:59'),
              'post_date_gmt'							=> gmdate('Y-m-d H:i:59'),
              'post_content'							=> $postData['post_content'],
              'post_title'								=> $postData['post_title'],
              'post_excerpt'							=> '',
              'post_status'								=> 'publish',
              'comment_status'						=> 'open',
              'ping_status'								=> 'open',
              'post_password'							=> '',
              'post_name'								  => $postName,
              'to_ping'								    => '',
              'pinged'								    => '',
              'post_modified'							=> gmdate('Y-m-d H:i:59'),
              'post_modified_gmt'					=> gmdate('Y-m-d H:i:59'),
              'post_content_filtered'			=> '',
              'post_parent'								=> 0,
              'guid'											=> '', /// link
              'menu_order'								=> 0,
              'post_type'									=> isset($postData['post_type']) ? $postData['post_type'] : 'post',
              'post_mime_type'						=> '',
              'comment_count'							=> 0,
    ];
    $query = $wpdb->prepare( "
        INSERT INTO {$wpdb->posts}
            VALUES (
                null,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s
            )
        ",
          $data['post_author'],
          $data['post_date'],
          $data['post_date_gmt'],
          $data['post_content'],
          $data['post_title'],
          $data['post_excerpt'],
          $data['post_status'],
          $data['comment_status'],
          $data['ping_status'],
          $data['post_password'],
          $data['post_name'],
          $data['to_ping'],
          $data['pinged'],
          $data['post_modified'],
          $data['post_modified_gmt'],
          $data['post_content_filtered'],
          $data['post_parent'],
          $data['guid'],
          $data['menu_order'],
          $data['post_type'],
          $data['post_mime_type'],
          $data['comment_count']
    );
    $wpdb->query( $query );
    return $wpdb->insert_id;
  }

  public function doesStudentCanSeeQandaSection($uid=0, $courseId=0)
  {
      if (is_admin()){
          return false;
      }
      if (empty($uid)){
          return false;
      }
      if (!get_option('ulp_qanda_enabled')){
          return false;
      }
      if (empty($courseId)){
          return false;
      }

      /// user can see this??
      require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
      $DbUserEntitiesRelations = new \DbUserEntitiesRelations();
      $isEnrolled = $DbUserEntitiesRelations->isUserEnrolledOnCourse($uid, $courseId);
      if (empty($isEnrolled)){
          return false;
      }
      return true;
  }

}
