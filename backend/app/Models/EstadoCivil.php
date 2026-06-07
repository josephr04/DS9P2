<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EstadoCivil extends Model {
    protected $connection = 'mysql';
    protected $table = 'estadocivil';
    protected $primaryKey = 'idEstadoCivil';
    protected $fillable = ['nombreEstadoCiv'];
    public $timestamps = false;
}