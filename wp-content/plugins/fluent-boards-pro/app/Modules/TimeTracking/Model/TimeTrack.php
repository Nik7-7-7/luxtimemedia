<?php

namespace FluentBoardsPro\App\Modules\TimeTracking\Model;

use FluentBoards\App\Models\Board;
use FluentBoards\App\Models\Model;
use FluentBoards\App\Models\Task;
use FluentBoards\App\Models\User;

class TimeTrack extends Model
{
    protected $table = 'fbs_time_tracks';

    protected $guarded = ['id'];

    protected $casts = [
        'working_minutes'  => 'int',
        'billable_minutes' => 'int',
        'is_manual'        => 'int',
    ];

    protected $fillable = [
        'user_id',
        'board_id',
        'task_id',
        'started_at',
        'completed_at',
        'status',
        'working_minutes',
        'billable_minutes',
        'is_manual',
        'message'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->user_id = $model->user_id ?: get_current_user_id();
            $model->started_at = $model->started_at ?: current_time('mysql');
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'ID');
    }

    public function board()
    {
        return $this->belongsTo(Board::class, 'board_id', 'id');
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'id');
    }

    public function getWorkingSeconds()
    {
        $workingSeconds = $this->working_minutes * 60;

        if ($this->status == 'active') {
            $workingSeconds += (current_time('timestamp') - strtotime($this->started_at));
        }

        return $workingSeconds;
    }


    /**
     * Get the attributes that have been changed since last sync.
     *
     * @return array
     */
    public function getDirty()
    {
        $dirty = [];
        foreach ($this->attributes as $key => $value) {
            if (!in_array($key, $this->fillable)) {
                continue;
            }

            if (!$this->originalIsEquivalent($key)) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }
}
