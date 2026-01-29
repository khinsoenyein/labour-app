<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
        'service_type_id',
        'status',
        'submitted_at',
        'remark',
    ];


    protected $casts = [
        'submitted_at' => 'datetime',
    ];


    // Applicant (Customer)
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    // Selected service type
    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }


    // Uploaded documents
    public function documents()
    {
        return $this->hasMany(ServiceRequestDocument::class);
    }


    // Change & audit history
    public function changes()
    {
        return $this->hasMany(ServiceRequestChange::class);
    }
}
