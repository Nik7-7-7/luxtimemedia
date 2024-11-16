<?php

namespace FluentBoardsPro\App\Models;

use FluentBoards\App\Models\Board;
use FluentBoards\App\Models\BoardTerm;
use FluentBoards\App\Models\Task;
use FluentBoards\Framework\Database\Orm\Builder;
use FluentBoardsPro\App\Services\Constant;

class CustomField extends BoardTerm
{
    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->type = $model->type ?: 'custom-field'; // default type is label
        });

        static::addGlobalScope('type', function (Builder $builder) {
            $builder->where('type', '=', 'custom-field');
        });
    }

    public function tasks()
    {
        return $this->belongsToMany(
            Task::class,
            'fbs_relations',
            'foreign_id',
            'object_id'
        )->withTimestamps()
            ->wherePivot('object_type', Constant::TASK_CUSTOM_FIELD)
            ->withPivot('settings', 'preferences');
    }

    public function board()
    {
        return $this->belongsTo(Board::class, 'board_id', 'id');
    }
}