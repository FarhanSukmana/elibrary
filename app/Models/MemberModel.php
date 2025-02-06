<?php

namespace App\Models;

use CodeIgniter\Model;

class MemberModel extends Model
{
    protected $table = 'anggota';
    protected $primaryKey = 'id_anggota';
    protected $allowedFields = [
        'nama_anggota',
        'username',
        'password',
        'jk_anggota',
        'level_anggota',
        'alamat_anggota',
        'foto_url'
    ];
    
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected $validationRules = [
        'username' => 'required|is_unique[anggota.username]',
        'password' => 'required|min_length[6]',
    ];
    
    
    protected function hashPassword(array $data)
    {
        if (!isset($data['data']['password'])) return $data;
        
        $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        return $data;
    }
}
