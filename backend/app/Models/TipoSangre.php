<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TipoSangre extends Model {
    protected $connection = 'mysql';
    protected $table = 'tiposangre';
    protected $primaryKey = 'idTipoSangre';
    protected $fillable = ['nombreTipoSangre'];
    public $timestamps = false;
}