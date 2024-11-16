<?php

namespace MasterStudy\Lms\Pro\addons\assignments\Repositories;

use MasterStudy\Lms\Plugin\PostType;
use MasterStudy\Lms\Repositories\AbstractRepository;

final class AssignmentRepository extends AbstractRepository {
	protected static array $fields_post_map = array(
		'title'   => 'post_title',
		'content' => 'post_content',
	);

	protected static array $fields_meta_map = array(
		'attempts' => 'assignment_tries',
	);

	protected static array $casts = array(
		'attempts' => 'int',
	);

	protected static string $post_type = PostType::ASSIGNMENT;
}
