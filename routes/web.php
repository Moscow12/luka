<?php

use App\Livewire\Acc\Payroll\Allowancepayment;
use App\Livewire\Acc\Payroll\Paymentreports;
use App\Livewire\Acc\Payroll\Payrollgeneration;
use App\Livewire\Chop\ActivitiesManagement;
use App\Livewire\Chop\Chopsetting;
use App\Livewire\Contracts\ContractDetails;
use App\Livewire\Contracts\ContractForm;
use App\Livewire\Contracts\ManageContracts;
use App\Livewire\Hr\Attendance\Fpdevices;
use App\Livewire\Hr\Attendance\Managefpattendance;
use App\Livewire\Hr\Attendance\Managefpusers;
use App\Livewire\Hr\Leave\Leaveapproval;
use App\Livewire\Hr\Leave\Leavebalance;
use App\Livewire\Hr\Leave\Leavemanagement;
use App\Livewire\Hr\Leave\Requestleave;
use App\Livewire\Hr\Loan\Loanapproval;
use App\Livewire\Hr\Loan\Loanbalance;
use App\Livewire\Hr\Loan\Loanitems;
use App\Livewire\Hr\Loan\Loanpayments;
use App\Livewire\Hr\Loan\Requestloan;
use App\Livewire\Hr\Roster\Editroster;
use App\Livewire\Hr\Roster\Generateroster;
use App\Livewire\Hr\Roster\Viewroster;
use App\Livewire\Hr\Staffs\Attendance;
use App\Livewire\Hr\Staffs\Contracts;
use App\Livewire\Hr\Staffs\Dependants;
use App\Livewire\Hr\Staffs\Digitalsignature;
use App\Livewire\Hr\Staffs\Disciplinary;
use App\Livewire\Hr\Staffs\Importstaffs;
use App\Livewire\Hr\Staffs\Leave;
use App\Livewire\Hr\Staffs\Otherdocuments;
use App\Livewire\Hr\Staffs\Promotions;
use App\Livewire\Hr\Staffs\Qualifications;
use App\Livewire\Hr\Staffs\Salary;
use App\Livewire\Performance\Staffs\Myduties;
use App\Livewire\Performance\Staffs\Myevaluations;
use App\Livewire\Performance\Staffs\Myimplimentations;
use App\Livewire\Performance\Staffs\Myperformanceview;
use App\Livewire\Performance\Staffs\Myplanning;
use App\Livewire\Performance\Supervisor\ApproveEvaluations;
use App\Livewire\Setup\Approvalconfigurations;
use App\Livewire\Setup\Location\Index;
use App\Models\loanrequests;
use Illuminate\Support\Facades\Route;

Route::get('/', App\Livewire\LandingPage::class)->name('dashboard')->middleware('auth');

Route::group(['prefix' => 'auth'], function () {
    Route::get('login', \App\Livewire\Auth\Login::class)->name('login')->middleware('guest');
    Route::get('forgot-password', \App\Livewire\Auth\ForgotPassword::class)->name('forgot-password')->middleware('guest');
    Route::get('reset-password', \App\Livewire\Auth\ResetPassword::class)->name('reset-password')->middleware('guest');
    Route::get('2fa', \App\Livewire\Auth\TwoFactorsAuthentication::class)->name('2fa')->middleware('guest');
    Route::get('logout', \App\Livewire\Auth\Logout::class)->name('logout')->middleware('auth');
});

Route::group(['prefix' => 'users', 'middleware' => 'auth'], function () {
    Route::get('profile', App\Livewire\Users\Profile\ProfileIndex::class)->name('user.profile');
    Route::get('change-password', App\Livewire\Users\ChangePassword::class)->name('user.change-password');
    Route::get('management', App\Livewire\Users\UserManagement::class)->name('user.management');
});

Route::group([
    'prefix' => 'acl',
    'middleware' => ['auth'],
], function () {
    Route::get('/', \App\Livewire\Acl\RoleIndex::class)->name('acl.index');
    Route::get('/create', \App\Livewire\Acl\RoleCreate::class)->name('acl.create');
    Route::get('/show/{role}', \App\Livewire\Acl\RoleShow::class)->name('acl.show');
    Route::get('/permissions', \App\Livewire\Acl\PermissionIndex::class)->name('acl.permissions');
});

Route::prefix('setup')->middleware('auth')->group(function () {
    Route::get('/settings', App\Livewire\Setup\Settings::class)->name('setup.index');
    Route::get('/setup/finance', App\Livewire\Setup\Finances::class)->name('setup.finances');
    Route::get('/', Index::class)->name('setup.location');
    Route::get('/setup/approvalconfigurations', Approvalconfigurations::class)->name('setup.approvalconfig');
    Route::get('/vendors', App\Livewire\Setup\VendorManagement::class)->name('setup.vendors');
    Route::get('/asset-configuration', App\Livewire\Setup\Asset\Assetconf::class)->name('setup.assetconfig');
});

Route::prefix('hr')->middleware('auth')->group(function () {
    Route::get('/overview', App\Livewire\Hr\Overview::class)->name('hr.index');
    Route::get('/staffs/stafflist', App\Livewire\Hr\Staffs\Stafflist::class)->name('hr.stafflist');
    Route::get('/staffs/addstaff', App\Livewire\Hr\Staffs\Addstaff::class)->name('hr.addstaff');
    Route::get('/staffs/addstaff/edit/{id}', App\Livewire\Hr\Staffs\Addstaff::class)->name('hr.editstaff');
    Route::get('/staffs/staffdetails/{id}', App\Livewire\Hr\Staffs\Staffdetails::class)->name('hr.staffdetails');
    Route::get('/staffs/contracts/{id}', Contracts::class)->name('hr.contracts');
    Route::get('/staffs/salary/{id}', Salary::class)->name('hr.salary');
    Route::get('staffs/qualifications/{id}', Qualifications::class)->name('hr.qualifications');
    Route::get('/staffs/import', Importstaffs::class)->name('hr.importstaffs');

    // promotions
    Route::get('/staffs/promotions/{id}', Promotions::class)->name('hr.promotions');
    Route::get('/staffs/disciplinary/{id}', Disciplinary::class)->name('hr.disciplinary');
    Route::get('/staffs/attendance/{id}', Attendance::class)->name('hr.attendance');
    Route::get('/staffs/leave/{id}', Leave::class)->name('hr.leave');
    Route::get('/staffs/dependants/{id}', Dependants::class)->name('hr.dependants');
    Route::get('/staffs/otherdocuments/{id}', Otherdocuments::class)->name('hr.otherdocuments');
    Route::get('/staffs/digitalsignature/{id}', Digitalsignature::class)->name('hr.digitalsignature');

    // attendance routes
    Route::get('/attendance/managefpattendance', Managefpattendance::class)->name('fp.attendance');
    Route::get('/attendance/managefpusers', Managefpusers::class)->name('managefpusers');
    Route::get('/attendance/fpdevices', Fpdevices::class)->name('fp.devices');

    // leave routes
    Route::get('leave/leaverequest', Requestleave::class)->name('leave.requestleave');
    Route::get('leave/leavebalance', Leavebalance::class)->name('leave.leavebalance');
    Route::get('leave/leaveapproval', Leaveapproval::class)->name('leave.leaveapproval');
    Route::get('/leave/leavemanagement', Leavemanagement::class)->name('leave.leavemanagement');

    // payroll routes
    Route::get('/payroll/payrollgeneration', Payrollgeneration::class)->name('payrollgeneration');
    Route::get('/payroll/allowancepayment', Allowancepayment::class)->name('allowancepayment');
    Route::get('/payroll/paymentreports', Paymentreports::class)->name('paymentreports');

    // employee roster routes
    Route::get('/roster/viewroster', Viewroster::class)->name('viewroster.index');
    Route::get('/roster/generateroster', Generateroster::class)->name('roster.create');
    Route::get('/roster/editroster', Editroster::class)->name('roster.edit');

    // employee loan requests
    Route::get('/loan/requestloan', Requestloan::class)->name('loan.requestloan');
    Route::get('/loan/items', Loanitems::class)->name('loan.items');
    Route::get('/loan/loanbalance', Loanbalance::class)->name('loan.loanbalance');
    Route::get('/loan/loanapproval', Loanapproval::class)->name('loan.loanapproval');
    Route::get('/loan/loanpayments', Loanpayments::class)->name('loan.loanpayments');

    // employee performance
    Route::get('/performance/overview', Myperformanceview::class)->name('performance.overview');
    // myplanning
    Route::get('/performance/myplanning', Myplanning::class)->name('performance.myplanning');
    // my implementation
    Route::get('/performance/myimplementation', Myimplimentations::class)->name('performance.myimplementation');
    // my evaluation
    Route::get('/performance/myevaluation', Myevaluations::class)->name('performance.myevaluation');
    // supervisor evaluation approval
    Route::get('/performance/approve-evaluations', ApproveEvaluations::class)->name('performance.approve.evaluations');
    // my duties
    Route::get('/performance/myduties', Myduties::class)->name('performance.myduties');
});

Route::prefix('contracts')->middleware('auth')->group(function () {
    // Institutional Contract Management
    Route::get('/', ManageContracts::class)->name('contracts.list');
    Route::get('/view/{contractId}', ContractDetails::class)->name('contracts.view');
    Route::get('/create', ContractForm::class)->name('contracts.create');
    Route::get('/edit/{contractId}', ContractForm::class)->name('contracts.edit');
});

Route::prefix('chop')->middleware('auth')->group(function () {
    // Chop Management
    Route::get('chopsettings', Chopsetting::class)->name('chop.settings');
    Route::get('activitiesmanagement', ActivitiesManagement::class)->name('chop.activities');

    // Budget Requests
    Route::get('budget-requests', App\Livewire\Chop\DepartmentBudgetRequest::class)->name('chop.budget.requests');
    Route::get('director-review', App\Livewire\Chop\DirectorReviewDashboard::class)->name('chop.director.review');

    // Cost Analysis
    Route::get('cost-analysis', App\Livewire\Chop\CostAnalysis::class)->name('chop.cost.analysis');

    // Monitoring & Evaluation
    Route::get('monitoring-evaluation', App\Livewire\Chop\MonitoringEvaluation::class)->name('chop.monitoring');

    // Activity Reporting
    Route::get('activity-reporting', App\Livewire\Chop\ActivityReporting::class)->name('chop.reporting');
});

Route::prefix('performance')->middleware('auth')->group(function () {
    // Organizational Plans
    Route::get('organizational-plans', App\Livewire\Performance\OrganizationalPlans\ManagePlans::class)->name('performance.org.plans');
    Route::get('organizational-plans/{planId}/items', App\Livewire\Performance\OrganizationalPlans\ManagePlanItems::class)->name('performance.org.plans.items');

    // Department Plans
    Route::get('department-plans', App\Livewire\Performance\DepartmentPlans\ManageDepartmentPlans::class)->name('performance.dept.plans');
    Route::get('department-plans/{planId}/items', App\Livewire\Performance\DepartmentPlans\ManagePlanItems::class)->name('performance.dept.plans.items');

    // Employee Plans
    Route::get('employee-plans', App\Livewire\Performance\EmployeePlans\ManageEmployeePlans::class)->name('performance.employee.plans');
    Route::get('employee-plans/{planId}/items', App\Livewire\Performance\EmployeePlans\ManagePlanItems::class)->name('performance.employee.plans.items');

    // Assigned Duties (Direct KPI Assignment)
    Route::get('assigned-duties', App\Livewire\Performance\AssignedDuties\ManageAssignedDuties::class)->name('performance.assigned.duties');

    // Job Title KPIs
    Route::get('title-kpis', App\Livewire\Performance\TitleKpis\ManageTitleKpis::class)->name('performance.title.kpis');
});

