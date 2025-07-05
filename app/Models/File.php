<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $table = "files";

    protected $primaryKey = 'id';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'id',
        'fileName',
        'extension',
        'url',
        'size',
        'ownerId',
        'tenantId',
        'created',
    ];

    public $timestamps = false;

    protected $casts = [
        'created' => 'datetime',
    ];
}
