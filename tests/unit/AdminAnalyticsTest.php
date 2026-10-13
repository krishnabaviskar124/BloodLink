<?php

use App\Models\BloodGroupModel;
use App\Models\BloodInventoryModel;
use App\Models\BloodRequestModel;
use App\Models\DonationModel;
use App\Models\DonorModel;
use App\Models\UserModel;
use App\Services\AnalyticsService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AdminAnalyticsTest extends CIUnitTestCase
{
    protected AnalyticsService $analyticsService;
    protected UserModel $userModel;
    protected DonorModel $donorModel;
    protected BloodRequestModel $requestModel;
    protected BloodInventoryModel $inventoryModel;
    protected BloodGroupModel $bloodGroupModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyticsService = new AnalyticsService();
        $this->userModel        = new UserModel();
        $this->donorModel       = new DonorModel();
        $this->requestModel     = new BloodRequestModel();
        $this->inventoryModel  = new BloodInventoryModel();
        $this->bloodGroupModel = new BloodGroupModel();
    }

    /**
     * TEST 1: Admin user role allowed dashboard evaluation.
     */
    public function testAdminCanAccessAnalyticsDashboard(): void
    {
        session()->set([
            'user_id'   => 999,
            'role_id'   => 1,
            'role_name' => 'Admin',
            'isLoggedIn'=> true,
        ]);

        $controller = new \App\Controllers\Admin();
        $controller->initController(service('request'), service('response'), service('logger'));

        $output = $controller->dashboard();
        $this->assertIsString((string) $output);
        $this->assertStringContainsString('Executive System Analytics', (string) $output);
    }

    /**
     * TEST 2: Donor role blocked from Admin analytics dashboard.
     */
    public function testDonorCannotAccessAnalyticsDashboard(): void
    {
        session()->set([
            'user_id'   => 888,
            'role_id'   => 2,
            'role_name' => 'Donor',
            'isLoggedIn'=> true,
        ]);

        $controller = new \App\Controllers\Admin();
        $controller->initController(service('request'), service('response'), service('logger'));

        $response = $controller->dashboard();
        $this->assertInstanceOf(\CodeIgniter\HTTP\RedirectResponse::class, $response);
    }

    /**
     * TEST 3: Hospital role blocked from Admin analytics dashboard.
     */
    public function testHospitalCannotAccessAnalyticsDashboard(): void
    {
        session()->set([
            'user_id'   => 777,
            'role_id'   => 3,
            'role_name' => 'Hospital',
            'isLoggedIn'=> true,
        ]);

        $controller = new \App\Controllers\Admin();
        $controller->initController(service('request'), service('response'), service('logger'));

        $response = $controller->dashboard();
        $this->assertInstanceOf(\CodeIgniter\HTTP\RedirectResponse::class, $response);
    }

    /**
     * TEST 4: Blood Bank Manager role blocked from Admin analytics dashboard.
     */
    public function testManagerCannotAccessAnalyticsDashboard(): void
    {
        session()->set([
            'user_id'   => 666,
            'role_id'   => 4,
            'role_name' => 'Blood Bank Manager',
            'isLoggedIn'=> true,
        ]);

        $controller = new \App\Controllers\Admin();
        $controller->initController(service('request'), service('response'), service('logger'));

        $response = $controller->dashboard();
        $this->assertInstanceOf(\CodeIgniter\HTTP\RedirectResponse::class, $response);
    }

    /**
     * TEST 5: Total donor count accuracy in summary metrics.
     */
    public function testTotalDonorCountAccuracy(): void
    {
        $metrics = $this->analyticsService->getSummaryMetrics();
        $this->assertArrayHasKey('totalDonors', $metrics);
        $this->assertIsInt($metrics['totalDonors']);
        $this->assertGreaterThanOrEqual(0, $metrics['totalDonors']);
    }

    /**
     * TEST 6: Available donor count accuracy in summary metrics.
     */
    public function testAvailableDonorCountAccuracy(): void
    {
        $metrics = $this->analyticsService->getSummaryMetrics();
        $this->assertArrayHasKey('availableDonors', $metrics);
        $this->assertIsInt($metrics['availableDonors']);
        $this->assertGreaterThanOrEqual(0, $metrics['availableDonors']);
        $this->assertLessThanOrEqual($metrics['totalDonors'], $metrics['availableDonors']);
    }

    /**
     * TEST 7: Eligible donor count accuracy in summary metrics.
     */
    public function testEligibleDonorCountAccuracy(): void
    {
        $metrics = $this->analyticsService->getSummaryMetrics();
        $this->assertArrayHasKey('eligibleDonors', $metrics);
        $this->assertIsInt($metrics['eligibleDonors']);
        $this->assertGreaterThanOrEqual(0, $metrics['eligibleDonors']);
    }

    /**
     * TEST 8: Blood group donor analytics structure.
     */
    public function testBloodGroupAnalyticsStructure(): void
    {
        $bgAnalytics = $this->analyticsService->getBloodGroupDonorAnalytics();
        $this->assertIsArray($bgAnalytics);
        $this->assertNotEmpty($bgAnalytics);

        foreach ($bgAnalytics as $row) {
            $this->assertArrayHasKey('name', $row);
            $this->assertArrayHasKey('registered', $row);
            $this->assertArrayHasKey('available', $row);
            $this->assertArrayHasKey('eligible', $row);
        }
    }

    /**
     * TEST 9: Blood request analytics structure.
     */
    public function testBloodRequestAnalyticsStructure(): void
    {
        $reqAnalytics = $this->analyticsService->getBloodGroupRequestAnalytics();
        $this->assertIsArray($reqAnalytics);
        $this->assertNotEmpty($reqAnalytics);

        foreach ($reqAnalytics as $row) {
            $this->assertArrayHasKey('name', $row);
            $this->assertArrayHasKey('total', $row);
            $this->assertArrayHasKey('pending', $row);
            $this->assertArrayHasKey('approved', $row);
            $this->assertArrayHasKey('fulfilled', $row);
            $this->assertArrayHasKey('cancelled', $row);
        }
    }

    /**
     * TEST 10: Urgency analytics structure.
     */
    public function testUrgencyAnalyticsStructure(): void
    {
        $urgency = $this->analyticsService->getUrgencyAnalytics();
        $this->assertArrayHasKey('critical', $urgency);
        $this->assertArrayHasKey('urgent', $urgency);
        $this->assertArrayHasKey('normal', $urgency);
        $this->assertArrayHasKey('total', $urgency);
        $this->assertSame($urgency['total'], $urgency['critical'] + $urgency['urgent'] + $urgency['normal']);
    }

    /**
     * TEST 11: City-wise demand analytics structure.
     */
    public function testCityWiseDemandAnalytics(): void
    {
        $cityDemand = $this->analyticsService->getCityDemandAnalytics();
        $this->assertIsArray($cityDemand);
        foreach ($cityDemand as $c) {
            $this->assertArrayHasKey('city', $c);
            $this->assertArrayHasKey('request_count', $c);
        }
    }

    /**
     * TEST 12: Integrated inventory analytics structure.
     */
    public function testInventoryAnalyticsStructure(): void
    {
        $inv = $this->analyticsService->getInventoryAnalytics();
        $this->assertArrayHasKey('totals', $inv);
        $this->assertArrayHasKey('totalUnits', $inv);
        $this->assertArrayHasKey('lowStockCount', $inv);
        $this->assertArrayHasKey('outOfStockCount', $inv);
        $this->assertIsInt($inv['totalUnits']);
    }

    /**
     * TEST 13: Recent activity payload format.
     */
    public function testRecentActivityPayload(): void
    {
        $activity = $this->analyticsService->getRecentActivity(5);
        $this->assertArrayHasKey('recentUsers', $activity);
        $this->assertArrayHasKey('recentRequests', $activity);
        $this->assertArrayHasKey('recentDonations', $activity);
        $this->assertArrayHasKey('recentNotifications', $activity);
    }

    /**
     * TEST 14: Charts receive correct real data payloads.
     */
    public function testChartsReceiveRealData(): void
    {
        session()->set([
            'user_id'   => 999,
            'role_id'   => 1,
            'role_name' => 'Admin',
            'isLoggedIn'=> true,
        ]);

        $controller = new \App\Controllers\Admin();
        $controller->initController(service('request'), service('response'), service('logger'));

        $html = (string) $controller->dashboard();
        $this->assertStringContainsString('bloodGroupDemandChart', $html);
        $this->assertStringContainsString('requestStatusChart', $html);
        $this->assertStringContainsString('urgencyDistributionChart', $html);
        $this->assertStringContainsString('cityDemandChart', $html);
    }

    /**
     * TEST 15: Empty database / filter conditions handled safely.
     */
    public function testEmptyDatabaseHandling(): void
    {
        // Pass non-existent date range and non-existent city
        $metrics = $this->analyticsService->getSummaryMetrics('1990-01-01', '1990-01-02', 9999, 'NonExistentCityLocation999');
        $this->assertSame(0, $metrics['totalDonors']);
        $this->assertSame(0, $metrics['totalRequests']);

        $urgency = $this->analyticsService->getUrgencyAnalytics('1990-01-01', '1990-01-02', 9999, 'NonExistentCityLocation999');
        $this->assertSame(0, $urgency['total']);
        $this->assertSame(0, $urgency['critical']);
    }
}
