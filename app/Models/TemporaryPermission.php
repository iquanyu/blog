<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemporaryPermission extends Model
{
    use HasFactory;
    
    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'permission',
        'conditions',
        'expires_at',
        'granted_by',
        'reason'
    ];
    
    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        'conditions' => 'array',
        'expires_at' => 'datetime',
    ];
    
    /**
     * 获取拥有此临时权限的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * 获取授予此权限的用户
     */
    public function grantor()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
    
    /**
     * 检查临时权限是否已过期
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }
    
    /**
     * 检查条件是否匹配
     *
     * @param array $checkConditions
     * @return bool
     */
    public function conditionsMatch(array $checkConditions = [])
    {
        // 如果没有条件限制，匹配所有
        if (empty($this->conditions)) {
            return true;
        }
        
        // 如果有条件但没有要检查的条件，不匹配
        if (empty($checkConditions)) {
            return false;
        }
        
        // 检查每个条件是否匹配
        foreach ($this->conditions as $key => $value) {
            if (!isset($checkConditions[$key]) || $checkConditions[$key] != $value) {
                return false;
            }
        }
        
        return true;
    }
}
