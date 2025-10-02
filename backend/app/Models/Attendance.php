<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    /**
     * attendancesテーブル
     */
    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'remarks',
        'status',
        'submitted_at',
        'approved_at',
    ];

    /**
     * リレーション: Attendance は 1人の User に属する
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * リレーション: Attendance は複数の BreakTime を持つ
     */
    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }
}
