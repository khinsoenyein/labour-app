<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestChange extends Model
{
    use HasFactory;


    protected $fillable = [
        'service_request_id',
        'field_name',
        'old_value',
        'new_value',
        'changed_by',
        'remark',
    ];


    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }


    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
