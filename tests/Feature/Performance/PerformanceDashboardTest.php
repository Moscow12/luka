<?php

use App\Livewire\Performance\Dashboard\PerformanceDashboard as DashboardComponent;
use App\Models\PerformanceEvaluation;
use App\Services\Performance\PerformanceDashboard;
use Carbon\Carbon;
use Livewire\Livewire;
use Tests\Support\PerformanceTestCase;

uses(PerformanceTestCase::class);

/*
 * One organisation goal (100 clients, weight 60) delivered by two departments, plus a
 * satisfaction goal (weight 40) nobody has picked up yet. Halfway through the year.
 *
 *   Ops:     dept target 60 -> Asha 30 (30 verified, 10 pending), Baraka 30 (15 verified)
 *   Finance: dept target 40 -> Chausiku 20 (20 verified)   <- only half the target assigned
 */
beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-07-02'));

    $this->plan = $this->makeOrgPlan();
    $this->clients = $this->makeOrgGoal($this->plan, ['weight' => 60]);
    $this->satisfaction = $this->makeOrgGoal($this->plan, ['item_name' => 'Client satisfaction', 'weight' => 40, 'target_value' => 90, 'target_unit' => '%']);

    $this->ops = $this->makeDepartment('Operations');
    $this->finance = $this->makeDepartment('Finance');

    $this->opsPlan = $this->makeDeptPlan($this->plan, $this->ops);
    $this->financePlan = $this->makeDeptPlan($this->plan, $this->finance);
    $opsGoal = $this->makeDeptGoal($this->opsPlan, $this->clients, ['target_value' => 60]);
    $financeGoal = $this->makeDeptGoal($this->financePlan, $this->clients, ['target_value' => 40]);

    $this->asha = $this->makeStaff($this->ops, [], ['first_name' => 'Asha']);
    $this->baraka = $this->makeStaff($this->ops, [], ['first_name' => 'Baraka']);
    $this->chausiku = $this->makeStaff($this->finance, [], ['first_name' => 'Chausiku']);

    $ashaGoal = $this->makeEmployeeGoal($opsGoal, $this->employeeOf($this->asha), 30);
    $this->logWork($ashaGoal, 30, 'verified', 8);
    $this->logWork($ashaGoal, 10, 'pending');

    $this->logWork($this->makeEmployeeGoal($opsGoal, $this->employeeOf($this->baraka), 30), 15);
    $this->logWork($this->makeEmployeeGoal($financeGoal, $this->employeeOf($this->chausiku), 20), 20);

    $this->ashaPlan = $ashaGoal->employeePlan;
    PerformanceEvaluation::create([
        'employee_plan_id' => $this->ashaPlan->id,
        'employee_id' => $this->ashaPlan->employee_id,
        'final_score' => 92,
        'status' => 'approved',
    ]);

    $this->hro = $this->makeStaff($this->ops, ['view-performance', 'view-all-dept-plans']);
});

it('rolls verified work up from employees to the organisation', function () {
    $org = (new PerformanceDashboard($this->plan, $this->hro))->organisation();
    $clients = collect($org['goals'])->firstWhere('id', $this->clients->id);
    $satisfaction = collect($org['goals'])->firstWhere('id', $this->satisfaction->id);

    // 30 + 15 + 20 verified; pending work does not count
    expect($clients['achieved'])->toEqual(65)
        ->and($clients['progress'])->toEqual(65)
        ->and($clients['departments'])->toBe(2)
        ->and($clients['status'])->toBe('on_track')
        ->and($satisfaction['progress'])->toEqual(0)
        ->and($satisfaction['departments'])->toBe(0)
        // (65 x 60 + 0 x 40) / 100
        ->and($org['progress'])->toEqual(39)
        ->and($org['status'])->toBe('at_risk')   // 39 is within 25 points of the expected 50
        ->and($org['activity'])->toMatchArray(['verified' => 3, 'pending' => 1])
        ->and($org['evaluations']['stages']['approved']['count'])->toBe(1)
        ->and($org['evaluations']['ratings']['Outstanding'])->toBe(1);
});

it('measures each department against its own target', function () {
    $dashboard = new PerformanceDashboard($this->plan, $this->hro);
    $ops = $dashboard->department($this->opsPlan->id);
    $finance = $dashboard->department($this->financePlan->id);

    expect($ops['progress'])->toEqual(75)        // 45 of 60
        ->and($ops['goals'][0]['coverage'])->toEqual(100)
        ->and($ops['goals'][0]['contributors'])->toBe(2)
        ->and($ops['pending'])->toBe(1)
        ->and(collect($ops['employees'])->pluck('employee_name')->all())->toContain('Asha '.$this->asha->surname)
        ->and($finance['progress'])->toEqual(50)  // 20 of 40, although Chausiku finished their share
        ->and($finance['goals'][0]['coverage'])->toEqual(50);
});

it('lists planning gaps for HR to follow up', function () {
    $issues = collect((new PerformanceDashboard($this->plan, $this->hro))->organisation()['issues'])->pluck('text');

    expect($issues->implode("\n"))
        ->toContain('"Client satisfaction" is not assigned to any department')
        ->toContain('only 50% of the "Clients served" target is assigned to staff')
        ->toContain('1 implementation entry is waiting for supervisor verification');
});

it('shows an employee their goals, evidence trail and evaluation', function () {
    $employee = (new PerformanceDashboard($this->plan, $this->hro))->employee($this->ashaPlan->id);

    expect($employee['progress'])->toEqual(100)
        ->and($employee['goals'][0]['quality'])->toEqual(8)
        ->and($employee['evaluation']['rating'])->toBe('Outstanding')
        ->and($employee['recent'])->toHaveCount(2)
        ->and($employee['parent_names'])->toContain('Clients served');
});

it('renders the organisation level and drills down to an employee', function () {
    $this->actingAs($this->hro);

    Livewire::test(DashboardComponent::class)
        ->assertSet('planId', $this->plan->id)
        ->assertSee('Organisation goals')
        ->assertSee($this->finance->name)
        ->call('openDepartment', $this->opsPlan->id)
        ->assertSee('Staff plans')
        ->assertSee('Asha')
        ->call('openEmployee', $this->ashaPlan->id)
        ->assertSee('Goals and measurable results')
        ->assertSee('Outstanding')
        ->call('showOrganisation')
        ->assertSet('departmentPlanId', null);
});

it('keeps department managers inside their own department', function () {
    $manager = $this->makeStaff($this->finance, ['manage-dept-plans']);
    $this->actingAs($manager);

    Livewire::test(DashboardComponent::class)
        ->assertSet('departmentPlanId', $this->financePlan->id)
        ->assertDontSee('Organisation goals')
        ->assertSee('Chausiku')
        ->call('openDepartment', $this->opsPlan->id)
        ->assertSet('departmentPlanId', $this->financePlan->id)
        ->assertDontSee('Asha');
});

it('is closed to staff without performance permissions', function () {
    $this->actingAs($this->baraka);

    Livewire::test(DashboardComponent::class)->assertForbidden();
});
