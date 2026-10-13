<?php

namespace App\Controllers;

use App\Models\BloodGroupModel;
use App\Models\DonorModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class Auth extends BaseController
{
    protected UserModel $userModel;
    protected DonorModel $donorModel;
    protected BloodGroupModel $bloodGroupModel;

    public function __construct()
    {
        $this->userModel        = new UserModel();
        $this->donorModel       = new DonorModel();
        $this->bloodGroupModel = new BloodGroupModel();
    }

    /**
     * Show Login Page or process Login POST request.
     */
    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return $this->redirectUserByRole(session()->get('role_name'));
        }

        return view('auth/login');
    }

    /**
     * Process user login request.
     */
    public function processLogin()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user = $this->userModel->getUserByEmail($email);

        if (! $user) {
            session()->setFlashdata('error', 'Invalid email or password.');
            return redirect()->back()->withInput();
        }

        if ($user['status'] !== 'active') {
            session()->setFlashdata('error', 'Your account is currently inactive or suspended. Please contact support.');
            return redirect()->back()->withInput();
        }

        if (! password_verify($password, $user['password'])) {
            session()->setFlashdata('error', 'Invalid email or password.');
            return redirect()->back()->withInput();
        }

        // Retrieve user with role name
        $userWithRole = $this->userModel->getUserWithRole($user['id']);

        // Secure session handling
        session()->regenerate();
        session()->set([
            'isLoggedIn' => true,
            'user_id'    => $user['id'],
            'name'       => $user['name'],
            'email'      => $user['email'],
            'role_id'    => $user['role_id'],
            'role_name'  => $userWithRole['role_name'] ?? 'Donor',
            'city'       => $user['city'],
        ]);

        session()->setFlashdata('success', 'Welcome back, ' . esc($user['name']) . '!');

        return $this->redirectUserByRole($userWithRole['role_name'] ?? 'Donor');
    }

    /**
     * Show Donor Registration Page.
     */
    public function registerDonor()
    {
        if (session()->get('isLoggedIn')) {
            return $this->redirectUserByRole(session()->get('role_name'));
        }

        $data['bloodGroups'] = $this->bloodGroupModel->findAll();

        return view('auth/register_donor', $data);
    }

    /**
     * Process Donor Registration.
     */
    public function processRegisterDonor()
    {
        $rules = [
            'name'           => 'required|min_length[2]|max_length[100]',
            'email'          => 'required|valid_email|max_length[100]|is_unique[users.email]',
            'password'       => 'required|min_length[6]|max_length[255]',
            'pass_confirm'   => 'required|matches[password]',
            'phone'          => 'required|max_length[20]',
            'city'           => 'required|max_length[100]',
            'blood_group_id' => 'required|is_natural_no_zero|is_not_unique[blood_groups.id]',
            'gender'         => 'permit_empty|in_list[male,female,other]',
            'date_of_birth'  => 'permit_empty|valid_date',
            'address'        => 'permit_empty',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // 2 = Donor Role
        $roleId = 2;

        $userData = [
            'role_id'  => $roleId,
            'name'     => $this->request->getPost('name'),
            'email'    => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'phone'    => $this->request->getPost('phone'),
            'city'     => $this->request->getPost('city'),
            'status'   => 'active',
        ];

        $userId = $this->userModel->insert($userData);

        $donorData = [
            'user_id'             => $userId,
            'blood_group_id'      => $this->request->getPost('blood_group_id'),
            'gender'              => $this->request->getPost('gender') ?: null,
            'date_of_birth'       => $this->request->getPost('date_of_birth') ?: null,
            'medical_eligibility' => 'eligible',
            'last_donation_date'  => $this->request->getPost('last_donation_date') ?: null,
            'address'             => $this->request->getPost('address'),
            'city'                => $this->request->getPost('city'),
        ];

        $this->donorModel->insert($donorData);

        $db->transComplete();

        if ($db->transStatus() === false) {
            session()->setFlashdata('error', 'Registration failed. Please try again.');
            return redirect()->back()->withInput();
        }

        session()->setFlashdata('success', 'Donor registration successful! Please log in with your credentials.');

        return redirect()->to('/login');
    }

    /**
     * Show Hospital Registration Page.
     */
    public function registerHospital()
    {
        if (session()->get('isLoggedIn')) {
            return $this->redirectUserByRole(session()->get('role_name'));
        }

        return view('auth/register_hospital');
    }

    /**
     * Process Hospital Registration.
     */
    public function processRegisterHospital()
    {
        $rules = [
            'name'         => 'required|min_length[2]|max_length[100]',
            'email'        => 'required|valid_email|max_length[100]|is_unique[users.email]',
            'password'     => 'required|min_length[6]|max_length[255]',
            'pass_confirm' => 'required|matches[password]',
            'phone'        => 'required|max_length[20]',
            'city'         => 'required|max_length[100]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // 3 = Hospital Role
        $roleId = 3;

        $userData = [
            'role_id'  => $roleId,
            'name'     => $this->request->getPost('name'),
            'email'    => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'phone'    => $this->request->getPost('phone'),
            'city'     => $this->request->getPost('city'),
            'status'   => 'active',
        ];

        $this->userModel->insert($userData);

        session()->setFlashdata('success', 'Hospital account registration successful! Please log in.');

        return redirect()->to('/login');
    }

    /**
     * Log out current user and destroy session.
     */
    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Helper to redirect logged-in users to their role-specific dashboard.
     */
    protected function redirectUserByRole(?string $roleName)
    {
        return match (strtolower((string) $roleName)) {
            'admin'              => redirect()->to('/admin/dashboard'),
            'donor'              => redirect()->to('/donor/dashboard'),
            'hospital'           => redirect()->to('/hospital/dashboard'),
            'blood bank manager' => redirect()->to('/bloodbank/dashboard'),
            default              => redirect()->to('/'),
        };
    }
}
