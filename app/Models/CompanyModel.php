<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyModel extends Model
{
    protected $table = 'companies';
    protected $primaryKey = 'id';
    protected $allowedFields = ['title', 'logo'];

    /**
     * Get companies by IDs using whereIn.
     *
     * @param array $ids
     * @return array
     */
    public function getCompaniesByIds(array $ids)
    {
        return $this->select('id, title, logo')
                    ->whereIn('id', $ids)
                    ->findAll();
    }
}
