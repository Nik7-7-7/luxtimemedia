<?php

namespace FluentBoardsPro\App\Models;

use FluentBoards\App\Models\Task;

use FluentBoards\App\Models\Attachment;
use FluentBoards\App\Services\Constant;

class TaskAttachment extends Attachment
{
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $uid = wp_generate_uuid4();
            $model->file_hash = md5($uid . mt_rand(0, 1000));
        });
        static::created(function ($model) {
            do_action('fluent_boards/task_attachment_added', $model);
        });
    }

//    public function scopeWithTask($query)
//    {
//        error_log('from attachment ' .__LINE__ . ' :'. print_r($query, true));
//
//        return $query->where('object_type', 'TASK')->with('task', function ($query) {
//            $query->select('id', 'title', 'board_id');
//        });
//    }

    public function getSecureUrlAttribute()
    {
        if ($this->attachment_type === 'url') {
            return $this->full_url;
        }
        return add_query_arg([
            'fbs'               => 1,
            'fbs_attachment'    => $this->file_hash,
            'secure_sign' => md5($this->id . date('YmdH'))
        ], site_url('/index.php'));
    }

    /**
     * Scope a query to include tasks related to attachments and descriptions.
     *
     * This scope filters the query to include records where the `object_type`
     * is either `TASK_ATTACHMENT` or `TASK_DESCRIPTION`. Additionally, it
     * includes related task data by using the `with` method to eager load
     * the `task` relationship, but only selects specific columns (`id`,
     * `title`, `board_id`) for efficiency.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithTask($query)
    {
        return $query->where(function ($query) {
            $query->where('object_type', Constant::TASK_ATTACHMENT)
                ->orWhere('object_type', Constant::TASK_DESCRIPTION)
                ->orWhere('object_type', Constant::COMMENT_IMAGE);
        })
            ->with(['task' => function ($query) {
                $query->select('id', 'title', 'board_id');
            }]);
    }


    public function task()
    {
        return $this->belongsTo(Task::class, 'object_id', 'id');
    }

}

