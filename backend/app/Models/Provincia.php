<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Provincia extends Model {
    protected $connection = 'mysql';
    protected $table = 'provincia';
    protected $primaryKey = 'codigo_provincia';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['codigo_provincia', 'nombre_provincia'];
    public $timestamps = false;
}