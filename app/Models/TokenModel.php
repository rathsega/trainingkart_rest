<?php
// app/Models/TokenModel.php
namespace App\Models;

use CodeIgniter\Model;

class TokenModel extends Model
{
    protected $table = 'tokens';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'token', 'expiry_date'];
}
