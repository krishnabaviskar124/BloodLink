<?php

namespace App\Controllers;

use App\Models\BloodGroupModel;
use App\Models\DonationModel;
use App\Models\UserModel;

class Donation extends BaseController
{
    protected DonationModel $donationModel;
    protected BloodGroupModel $bloodGroupModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->donationModel    = new DonationModel();
        $this->bloodGroupModel = new BloodGroupModel();
        $this->userModel        = new UserModel();
    }

    /**
     * List donation records according to user role.
     */
    public function index()
    {
        $userId   = session()->get('user_id');
        $userRole = strtolower((string) session()->get('role_name'));

        if ($userRole === 'donor') {
            // Donors see their own history
            $donations = $this->donationModel->getDonationsByDonor($userId);
        } else {
            // Admin & Blood Bank Manager see all donation records
            $donations = $this->donationModel->select('donations.*, users.name as donor_name, blood_groups.name as blood_group_name')
                                             ->join('users', 'users.id = donations.donor_id', 'left')
                                             ->join('blood_groups', 'blood_groups.id = donations.blood_group_id', 'left')
                                             ->orderBy('donations.donation_date', 'DESC')
                                             ->findAll();
        }

        return view('donations/index', ['donations' => $donations, 'userRole' => $userRole]);
    }

    /**
     * Show form to record a new donation (Authorized role only).
     */
    public function create()
    {
        // Donors only role ID is 2
        $donors      = $this->userModel->getUsersByRole(2);
        $bloodGroups = $this->bloodGroupModel->findAll();

        return view('donations/create', [
            'donors'      => $donors,
            'bloodGroups' => $bloodGroups,
        ]);
    }

    /**
     * Store new donation record.
     */
    public function store()
    {
        $rules = [
            'donor_id'       => 'required|is_natural_no_zero|is_not_unique[users.id]',
            'blood_group_id' => 'required|is_natural_no_zero|is_not_unique[blood_groups.id]',
            'donation_date'  => 'required|valid_date',
            'units_donated'  => 'required|is_natural_no_zero',
            'status'         => 'permit_empty|in_list[scheduled,completed,cancelled]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $donationData = [
            'donor_id'       => $this->request->getPost('donor_id'),
            'blood_group_id' => $this->request->getPost('blood_group_id'),
            'donation_date'  => $this->request->getPost('donation_date'),
            'units_donated'  => $this->request->getPost('units_donated'),
            'status'         => $this->request->getPost('status') ?: 'completed',
        ];

        $this->donationModel->insert($donationData);

        // Update donor's last_donation_date in donor_profiles
        $donorModel    = new \App\Models\DonorModel();
        $donorProfile  = $donorModel->where('user_id', $this->request->getPost('donor_id'))->first();

        if ($donorProfile) {
            $donorModel->update($donorProfile['id'], [
                'last_donation_date' => $this->request->getPost('donation_date'),
            ]);
        }

        session()->setFlashdata('success', 'New blood donation recorded successfully!');

        return redirect()->to('/donations');
    }

    /**
     * View donation record details.
     */
    public function show($id = null)
    {
        $donation = $this->donationModel->getDonationWithDetails((int) $id);

        if (! $donation) {
            session()->setFlashdata('error', 'Donation record not found.');
            return redirect()->to('/donations');
        }

        // Ownership Check: Donors can only view their own donation detail
        $userId   = session()->get('user_id');
        $userRole = strtolower((string) session()->get('role_name'));

        if ($userRole === 'donor' && $donation['donor_id'] != $userId) {
            session()->setFlashdata('error', 'Unauthorized access to donation record.');
            return redirect()->to('/donations');
        }

        return view('donations/show', ['donation' => $donation]);
    }
}
