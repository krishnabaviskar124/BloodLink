<?php

namespace App\Models;

use CodeIgniter\Model;

class DonorModel extends Model
{
    protected $table            = 'donor_profiles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'blood_group_id',
        'gender',
        'date_of_birth',
        'medical_eligibility',
        'availability',
        'last_donation_date',
        'address',
        'city',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'user_id'             => 'required|is_natural_no_zero|is_not_unique[users.id]|is_unique[donor_profiles.user_id,id,{id}]',
        'blood_group_id'      => 'required|is_natural_no_zero|is_not_unique[blood_groups.id]',
        'gender'              => 'permit_empty|in_list[male,female,other]',
        'date_of_birth'       => 'permit_empty|valid_date',
        'medical_eligibility' => 'permit_empty|in_list[eligible,ineligible,pending]',
        'availability'        => 'permit_empty|in_list[available,temporarily_unavailable,not_available]',
        'last_donation_date'  => 'permit_empty|valid_date',
        'address'             => 'permit_empty',
        'city'                => 'permit_empty|max_length[100]',
    ];

    protected $validationMessages = [
        'user_id' => [
            'is_unique'     => 'A donor profile already exists for this user.',
            'is_not_unique' => 'Associated user account does not exist.',
        ],
        'blood_group_id' => [
            'is_not_unique' => 'Selected blood group does not exist.',
        ],
    ];

    /**
     * Get donor profile by user ID with associated user details and blood group name.
     */
    public function getDonorByUserId(int $userId): ?array
    {
        return $this->select('donor_profiles.*, users.name as donor_name, users.email, users.phone, users.status as user_status, blood_groups.name as blood_group_name')
                    ->join('users', 'users.id = donor_profiles.user_id', 'left')
                    ->join('blood_groups', 'blood_groups.id = donor_profiles.blood_group_id', 'left')
                    ->where('donor_profiles.user_id', $userId)
                    ->first();
    }

    /**
     * Search eligible & available donors filtered optional blood group ID and city.
     */
    public function searchEligibleDonors(?int $bloodGroupId = null, ?string $city = null): array
    {
        $builder = $this->select('donor_profiles.*, users.name as donor_name, users.phone, users.email, blood_groups.name as blood_group_name')
                        ->join('users', 'users.id = donor_profiles.user_id', 'left')
                        ->join('blood_groups', 'blood_groups.id = donor_profiles.blood_group_id', 'left')
                        ->where('donor_profiles.medical_eligibility', 'eligible')
                        ->where('donor_profiles.availability', 'available')
                        ->where('users.status', 'active');

        if (! empty($bloodGroupId)) {
            $builder->where('donor_profiles.blood_group_id', $bloodGroupId);
        }

        if (! empty($city)) {
            $builder->like('donor_profiles.city', trim($city));
        }

        return $builder->findAll();
    }
}
