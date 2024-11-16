<?php
namespace Indeed\Ulp\Db;
if (!defined('ABSPATH')){
   exit();
}

class DbCourseTags
{

    public function __construct(){}

    public function getAll()
    {
        global $wpdb;
        $query = "
                  SELECT a.term_id, a.name, a.slug, b.meta_value as color
                    	FROM {$wpdb->prefix}terms a
                    	INNER JOIN {$wpdb->prefix}termmeta b
                    	ON a.term_id=b.term_id
                    	INNER JOIN {$wpdb->prefix}term_taxonomy c
                    	ON a.term_id=c.term_id
                    	WHERE
                    	c.taxonomy='course_tags'
                      AND
                      b.meta_key='color'
        ";
        $data = $wpdb->get_results($query);
        return $data;
    }

    public function getOne($termId=0, $defaultIfNull=false)
    {
      if (empty($termId) && $defaultIfNull){
          $data = new \stdClass();
          $data->term_id = 0;
          $data->name = '';
          $data->slug = '';
          $data->color = '';
          $data->description = '';
          return $data;
      }
      global $wpdb;
      $query = $wpdb->prepare("
                SELECT a.term_id, a.name, a.slug, b.meta_value as color
                    FROM {$wpdb->prefix}terms a
                    INNER JOIN {$wpdb->prefix}termmeta b
                    ON a.term_id=b.term_id
                    INNER JOIN {$wpdb->prefix}term_taxonomy c
                    ON a.term_id=c.term_id
                    WHERE
                    c.taxonomy='course_tags'
                    AND
                    a.term_id=%d
                    AND
                    b.meta_key='color'
      ", $termId );
      $data = $wpdb->get_row($query);
      return $data;
    }

    public function getAllByCourse($courseId=0)
    {
      global $wpdb;
      $query = $wpdb->prepare("
                SELECT a.term_id, a.name, a.slug, b.meta_value as color
                    FROM {$wpdb->prefix}terms a
                    INNER JOIN {$wpdb->prefix}termmeta b
                    ON a.term_id=b.term_id
                    INNER JOIN {$wpdb->prefix}term_taxonomy c
                    ON a.term_id=c.term_id
                    INNER JOIN {$wpdb->prefix}term_relationships d
                    ON c.term_taxonomy_id=d.term_taxonomy_id
                    WHERE
                    c.taxonomy='course_tags'
                    AND
                    b.meta_key='color'
                    AND
                    d.object_id=%d
      ", $courseId );
      $data = $wpdb->get_results($query);
      return $data;
    }

    public function save($attr=[])
    {
        $args = [
          'description'=> $attr['description'],
          'slug' => $attr['slug'],
        ];
        $taxonomy = 'course_tags';
        if (empty($attr['term_id'])){
            $termData = wp_insert_term($attr['label'], $taxonomy, $args);
            if (is_wp_error($termData)){
                return false;
            }
            $termId = isset($termData['term_id']) ? $termData['term_id'] : 0;
        } else {
            $result = wp_update_term( $attr['term_id'], $taxonomy, $args );
            if (is_wp_error($result)){
                return false;
            }
            $termId = $attr['term_id'];
        }

        if (!empty($termId)){
            update_term_meta($termId, 'color', $attr['color']);
            return $termId;
        }
        return 0;
    }

    public function delete($termId=0)
    {
        return wp_delete_term($termId, 'course_tags');
    }

}
