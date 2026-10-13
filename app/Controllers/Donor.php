<?php

namespace App\Controllers;

use App\Models\BloodGroupModel;
use App\Models\BloodRequestModel;
use App\Models\DonationModel;
use App\Models\DonorModel;
use App\Models\UserModel;

class Donor extends BaseController
{
    protected UserModel $userModel;
    protected DonorModel $donorModel;
    protected BloodGroupModel $bloodGroupModel;
    protected DonationModel $donationModel;
    protected BloodRequestModel $bloodRequestModel;

    public function __construct()
    {
        $this->userModel        = new UserModel();
        $this->donorModel       = new DonorModel();
        $this->bloodGroupModel = new BloodGroupModel();
        $this->donationModel    = new DonationModel();
        $this->bloodRequestModel = new BloodRequestModel();
    }

    /**
     * Donor Dashboard View
     */
    public function dashboard()
    {
        $userId = session()->get('user_id');
        $donor  = $this->donorModel->getDonorByUserId($userId);

        if (! $donor) {
            session()->setFlashdata('error', 'Donor profile not found. Please complete your profile.');
            return redirect()->to('/donor/profile/edit');
        }

        $donations = $this->donationModel->getDonationsByDonor($userId);
        
        // Matching pending requests for donor's blood group
        $matchingRequests = [];
        if (! empty($donor['blood_group_id'])) {
            $matchingRequests = $this->bloodRequestModel->getPendingRequests($donor['blood_group_id'], $donor['city']);
        }

        $contactRequestModel = new \App\Models\ContactRequestModel();
        $contactRequests     = $contactRequestModel->getContactRequestsForDonor($userId);

        $data = [
            'donor'            => $donor,
            'donationCount'    => count($donations),
            'recentDonations'  => array_slice($donations, 0, 5),
            'matchingRequests' => $matchingRequests,
            'contactRequests'  => $contactRequests,
        ];

        return view('donor/dashboard', $data);
    }

    /**
     * Update Donor Availability Status (Security enforced: logged in donor updates their own profile)
     */
    public function updateAvailability()
    {
        $userId = (int) session()->get('user_id');

        $newAvailability = $this->request->getPost('availability');
        $allowedValues   = ['available', 'temporarily_unavailable', 'not_available'];

        if (! in_array($newAvailability, $allowedValues, true)) {
            session()->setFlashdata('error', 'Invalid availability status selected.');
            return redirect()->back();
        }

        $donorProfile = $this->donorModel->where('user_id', $userId)->first();

        if (! $donorProfile) {
            session()->setFlashdata('error', 'Donor profile not found.');
            return redirect()->to('/donor/profile/edit');
        }

        // Security check: ensure target profile belongs strictly to authenticated user
        if ($donorProfile['user_id'] != $userId) {
            session()->setFlashdata('error', 'Unauthorized action: You cannot modify another donor\'s availability.');
            return redirect()->to('/donor/dashboard');
        }

        $this->donorModel->update($donorProfile['id'], [
            'availability' => $newAvailability,
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        $labels = [
            'available'               => 'Available for emergency matching',
            'temporarily_unavailable' => 'Temporarily Unavailable',
            'not_available'           => 'Not Available',
        ];

        session()->setFlashdata('success', 'Donation availability updated to: ' . $labels[$newAvailability]);

        return redirect()->to('/donor/dashboard');
    }

    /**
     * View Donor Profile
     */
    public function profile()
    {
        $userId = session()->get('user_id');
        $donor  = $this->donorModel->getDonorByUserId($userId);

        if (! $donor) {
            return redirect()->to('/donor/profile/edit');
        }

        return view('donor/profile', ['donor' => $donor]);
    }

    /**
     * Edit Donor Profile Form
     */
    public function editProfile()
    {
        $userId = session()->get('user_id');
        $donor  = $this->donorModel->getDonorByUserId($userId);
        $user   = $this->userModel->find($userId);

        $data = [
            'donor'       => $donor,
            'user'        => $user,
            'bloodGroups' => $this->bloodGroupModel->findAll(),
        ];

        return view('donor/edit_profile', $data);
    }

    /**
     * Process Donor Profile Update
     */
    public function updateProfile()
    {
        $userId = session()->get('user_id');

        $rules = [
            'name'           => 'required|min_length[2]|max_length[100]',
            'phone'          => 'required|max_length[20]',
            'city'           => 'required|max_length[100]',
            'blood_group_id' => 'required|is_natural_no_zero|is_not_unique[blood_groups.id]',
            'gender'         => 'permit_empty|in_list[male,female,other]',
            'date_of_birth'  => 'permit_empty|valid_date',
            'last_donation'  => 'permit_empty|valid_date',
            'address'        => 'permit_empty',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // 1. Update User basic info
        $this->userModel->update($userId, [
            'name'  => $this->request->getPost('name'),
            'phone' => $this->request->getPost('phone'),
            'city'  => $this->request->getPost('city'),
        ]);

        // Update session name & city
        session()->set('name', $this->request->getPost('name'));
        session()->set('city', $this->request->getPost('city'));

        // 2. Update/Insert Donor Profile
        $existingDonor = $this->donorModel->where('user_id', $userId)->first();

        $donorData = [
            'user_id'            => $userId,
            'blood_group_id'     => $this->request->getPost('blood_group_id'),
            'gender'             => $this->request->getPost('gender') ?: null,
            'date_of_birth'      => $this->request->getPost('date_of_birth') ?: null,
            'last_donation_date' => $this->request->getPost('last_donation') ?: null,
            'address'            => $this->request->getPost('address'),
            'city'               => $this->request->getPost('city'),
        ];

        if ($existingDonor) {
            $this->donorModel->update($existingDonor['id'], $donorData);
        } else {
            $donorData['medical_eligibility'] = 'eligible';
            $donorData['availability']        = 'available';
            $this->donorModel->insert($donorData);
        }

        session()->setFlashdata('success', 'Profile updated successfully!');

        return redirect()->to('/donor/profile');
    }

    /**
     * View Own Donation History
     */
    public function donations()
    {
        $userId    = session()->get('user_id');
        $donations = $this->donationModel->getDonationsByDonor($userId);

        return view('donor/donations', ['donations' => $donations]);
    }
}
