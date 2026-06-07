<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RangoAcademico extends Model {
    protected $connection = 'mysql';
    protected $table = 'rangoacademico';
    protected $primaryKey = 'idRangoEdu';
    protected $fillable = ['nombreRangoEdu'];
    public $timestamps = false;
}