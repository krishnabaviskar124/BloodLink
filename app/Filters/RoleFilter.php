<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    /**
     * Check if the logged-in user possesses the required role.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments Allowed role names (e.g. ['Admin', 'Donor'])
     *
     * @return ResponseInterface|void|mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Please log in to access this page.');
            return redirect()->to('/login');
        }

        if (! empty($arguments)) {
            $userRole = session()->get('role_name');

            // Normalize case-insensitive comparison
            $allowedRoles = array_map('strtolower', $arguments);
            $currentUserRole = strtolower((string) $userRole);

            if (! in_array($currentUserRole, $allowedRoles, true)) {
                session()->setFlashdata('error', 'Access denied: You do not have permission to view this page.');

                // Redirect to role-specific dashboard or home
                return match ($currentUserRole) {
                    'admin'              => redirect()->to('/admin/dashboard'),
                    'donor'              => redirect()->to('/donor/dashboard'),
                    'hospital'           => redirect()->to('/hospital/dashboard'),
                    'blood bank manager' => redirect()->to('/bloodbank/dashboard'),
                    default              => redirect()->to('/'),
                };
            }
        }
    }

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void|mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after request
    }
}
