<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestDocument extends Model
{
    use HasFactory;


    protected $fillable = [
        'service_request_id',
        'file_path',
        'document_type',
        'version',
        'mime_type',
        'file_size',
        'uploaded_by',
    ];


    protected $casts = [
        'file_size' => 'integer',
    ];


    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }


    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
