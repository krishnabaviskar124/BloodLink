<?php

namespace App\Models;

use CodeIgniter\Model;

class DonationModel extends Model
{
    protected $table            = 'donations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'donor_id',
        'blood_group_id',
        'donation_date',
        'units_donated',
        'status',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'donor_id'       => 'required|is_natural_no_zero|is_not_unique[users.id]',
        'blood_group_id' => 'required|is_natural_no_zero|is_not_unique[blood_groups.id]',
        'donation_date'  => 'required|valid_date',
        'units_donated'  => 'required|is_natural_no_zero',
        'status'         => 'permit_empty|in_list[scheduled,completed,cancelled]',
    ];

    protected $validationMessages = [
        'donor_id' => [
            'is_not_unique' => 'Donor user account does not exist.',
        ],
        'blood_group_id' => [
            'is_not_unique' => 'Selected blood group does not exist.',
        ],
    ];

    /**
     * Get donation records for a specific donor with blood group details.
     */
    public function getDonationsByDonor(int $donorId): array
    {
        return $this->select('donations.*, blood_groups.name as blood_group_name')
                    ->join('blood_groups', 'blood_groups.id = donations.blood_group_id', 'left')
                    ->where('donations.donor_id', $donorId)
                    ->orderBy('donations.donation_date', 'DESC')
                    ->findAll();
    }

    /**
     * Get single donation record with donor name and blood group name.
     */
    public function getDonationWithDetails(int $id): ?array
    {
        return $this->select('donations.*, users.name as donor_name, users.phone as donor_phone, blood_groups.name as blood_group_name')
                    ->join('users', 'users.id = donations.donor_id', 'left')
                    ->join('blood_groups', 'blood_groups.id = donations.blood_group_id', 'left')
                    ->where('donations.id', $id)
                    ->first();
    }
}
