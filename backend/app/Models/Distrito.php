<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Distrito extends Model {
    protected $connection = 'mysql';
    protected $table = 'distrito';
    protected $primaryKey = 'codigo_distrito';
    protected $fillable = ['codigo_provincia', 'codigo_distrito', 'codigo', 'nombre_distrito'];
    public $timestamps = false;
}