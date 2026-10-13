<?php

namespace App\Services;

class DonorEligibilityService
{
    /**
     * Minimum donation interval in days.
     */
    public const MIN_DONATION_INTERVAL_DAYS = 90;

    /**
     * Evaluate dynamic donor medical eligibility based on active user status, valid blood group, and donation date interval.
     *
     * Rules:
     * 1. Active donor + no previous donation = ELIGIBLE
     * 2. Donation exactly 90 days ago = ELIGIBLE
     * 3. Donation less than 90 days ago = NOT ELIGIBLE
     * 4. Donation older than 90 days = ELIGIBLE
     * 5. Future donation date = NOT ELIGIBLE
     * 6. Invalid/missing donation date = handled safely (missing/null -> ELIGIBLE; invalid date -> ELIGIBLE fallback)
     * 7. Inactive donor = NOT ELIGIBLE
     *
     * @param array $donor Donor profile data (with user_status/status, blood_group_id, last_donation_date, etc.)
     * @param string|null $referenceDate Reference evaluation date (Y-m-d), defaults to today
     * @return array [
     *     'is_eligible'    => bool,
     *     'status'         => 'eligible'|'ineligible',
     *     'label'          => 'ELIGIBLE'|'NOT ELIGIBLE',
     *     'reason'         => string,
     *     'days_elapsed'   => int|null,
     *     'days_remaining' => int|null,
     * ]
     */
    public function evaluateEligibility(array $donor, ?string $referenceDate = null): array
    {
        $refTimestamp = $referenceDate ? strtotime($referenceDate) : time();
        if ($refTimestamp === false) {
            $refTimestamp = time();
        }

        // Rule 7: Inactive donor = NOT ELIGIBLE
        $rawStatus = $donor['user_status'] ?? $donor['status'] ?? 'active';
        $userStatus = strtolower(trim((string) $rawStatus));
        if ($userStatus !== 'active') {
            return [
                'is_eligible'    => false,
                'status'         => 'ineligible',
                'label'          => 'NOT ELIGIBLE',
                'reason'         => 'Donor user account is inactive',
                'days_elapsed'   => null,
                'days_remaining' => null,
            ];
        }

        // Rule: Valid blood group required
        $bloodGroupId = (int) ($donor['blood_group_id'] ?? 0);
        if ($bloodGroupId <= 0) {
            return [
                'is_eligible'    => false,
                'status'         => 'ineligible',
                'label'          => 'NOT ELIGIBLE',
                'reason'         => 'No valid blood group assigned',
                'days_elapsed'   => null,
                'days_remaining' => null,
            ];
        }

        // Rule 1 & 6: Missing / null donation date = ELIGIBLE (first-time or ready donor)
        $lastDonationDate = $donor['last_donation_date'] ?? null;
        if (empty($lastDonationDate)) {
            return [
                'is_eligible'    => true,
                'status'         => 'eligible',
                'label'          => 'ELIGIBLE',
                'reason'         => 'First-time or ready donor (no prior donation recorded)',
                'days_elapsed'   => null,
                'days_remaining' => 0,
            ];
        }

        $donationTimestamp = strtotime((string) $lastDonationDate);

        // Rule 6: Invalid donation date = handle safely
        if ($donationTimestamp === false) {
            return [
                'is_eligible'    => true,
                'status'         => 'eligible',
                'label'          => 'ELIGIBLE',
                'reason'         => 'Invalid donation date format, defaulting safely to eligible',
                'days_elapsed'   => null,
                'days_remaining' => 0,
            ];
        }

        // Normalize timestamps to start of day for exact day arithmetic
        $refDay = strtotime(date('Y-m-d', $refTimestamp));
        $donDay = strtotime(date('Y-m-d', $donationTimestamp));

        // Rule 5: Future donation date = NOT ELIGIBLE
        if ($donDay > $refDay) {
            return [
                'is_eligible'    => false,
                'status'         => 'ineligible',
                'label'          => 'NOT ELIGIBLE',
                'reason'         => 'Donation date cannot be in the future',
                'days_elapsed'   => null,
                'days_remaining' => null,
            ];
        }

        // Days elapsed since last donation
        $daysElapsed = (int) round(($refDay - $donDay) / 86400);

        // Rules 2, 3, 4: Minimum donation interval 90 days
        if ($daysElapsed >= self::MIN_DONATION_INTERVAL_DAYS) {
            return [
                'is_eligible'    => true,
                'status'         => 'eligible',
                'label'          => 'ELIGIBLE',
                'reason'         => "Donated {$daysElapsed} days ago (meets 90-day minimum interval)",
                'days_elapsed'   => $daysElapsed,
                'days_remaining' => 0,
            ];
        }

        $daysRemaining = self::MIN_DONATION_INTERVAL_DAYS - $daysElapsed;
        return [
            'is_eligible'    => false,
            'status'         => 'ineligible',
            'label'          => 'NOT ELIGIBLE',
            'reason'         => "Donated {$daysElapsed} days ago. Must wait {$daysRemaining} more day(s) before next donation.",
            'days_elapsed'   => $daysElapsed,
            'days_remaining' => $daysRemaining,
        ];
    }

    /**
     * Check if a donor is eligible.
     */
    public function isEligible(array $donor, ?string $referenceDate = null): bool
    {
        return $this->evaluateEligibility($donor, $referenceDate)['is_eligible'];
    }

    /**
     * Get eligibility status ('eligible'|'ineligible').
     */
    public function getEligibilityStatus(array $donor, ?string $referenceDate = null): string
    {
        return $this->evaluateEligibility($donor, $referenceDate)['status'];
    }

    /**
     * Get eligibility label ('ELIGIBLE'|'NOT ELIGIBLE').
     */
    public function getEligibilityLabel(array $donor, ?string $referenceDate = null): string
    {
        return $this->evaluateEligibility($donor, $referenceDate)['label'];
    }
}
