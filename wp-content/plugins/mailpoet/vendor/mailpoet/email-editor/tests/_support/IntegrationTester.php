<?php declare(strict_types=1);
if (!defined('ABSPATH')) exit;
class IntegrationTester extends \Codeception\Actor
{
 use _generated\IntegrationTesterActions;
 private $wpTermIds = [];
 private $createdCommentIds = [];
 private $posts = [];
 public function createPost(array $params): \WP_Post {
 $postId = wp_insert_post($params);
 if ($postId instanceof WP_Error) {
 throw new \Exception('Failed to create post');
 }
 $post = get_post($postId);
 if (!$post instanceof WP_Post) {
 throw new \Exception('Failed to fetch the post');
 }
 $this->posts[] = $post;
 return $post;
 }
 public function cleanup() {
 $this->deletePosts();
 }
 private function deletePosts() {
 foreach ($this->posts as $post) {
 wp_delete_post($post->ID, true);
 }
 }
}
