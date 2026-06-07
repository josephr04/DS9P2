<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Institucion extends Model {
    protected $connection = 'mysql_documentos';
    protected $table = 'instituciones';
    protected $primaryKey = 'idInstitucion';
    protected $fillable = ['nombreInstitucion'];
    public $timestamps = false;
}