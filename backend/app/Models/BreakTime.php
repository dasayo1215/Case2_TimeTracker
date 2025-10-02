<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    /**
     * breaksテーブル
     */
    protected $fillable = [
        'attendance_id',
        'break_start',
        'break_end',
    ];

    /**
     * リレーション: BreakTime は 1つの Attendance に属する
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
