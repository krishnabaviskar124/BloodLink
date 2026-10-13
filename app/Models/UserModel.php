<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'role_id',
        'name',
        'email',
        'password',
        'phone',
        'city',
        'status',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'role_id'  => 'required|is_natural_no_zero|is_not_unique[roles.id]',
        'name'     => 'required|min_length[2]|max_length[100]',
        'email'    => 'required|valid_email|max_length[100]|is_unique[users.email,id,{id}]',
        'password' => 'required|min_length[6]|max_length[255]',
        'phone'    => 'permit_empty|max_length[20]',
        'city'     => 'permit_empty|max_length[100]',
        'status'   => 'permit_empty|in_list[active,inactive,suspended]',
    ];

    protected $validationMessages = [
        'email' => [
            'is_unique' => 'This email address is already registered.',
        ],
        'role_id' => [
            'is_not_unique' => 'Selected role does not exist.',
        ],
    ];

    /**
     * Get user details along with assigned role name.
     */
    public function getUserWithRole(int $userId): ?array
    {
        return $this->select('users.*, roles.name as role_name, roles.description as role_description')
                    ->join('roles', 'roles.id = users.role_id', 'left')
                    ->where('users.id', $userId)
                    ->first();
    }

    /**
     * Get user by email address.
     */
    public function getUserByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Get all users belonging to a specific role ID.
     */
    public function getUsersByRole(int $roleId): array
    {
        return $this->select('users.*, roles.name as role_name')
                    ->join('roles', 'roles.id = users.role_id', 'left')
                    ->where('users.role_id', $roleId)
                    ->findAll();
    }
}
