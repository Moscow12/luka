# SECTION 1: INTRODUCTION & SYSTEM OVERVIEW

## Table of Contents
- [About the System](#about-the-system)
- [Key Features & Modules](#key-features--modules)
- [System Architecture](#system-architecture)
- [User Roles Overview](#user-roles-overview)
- [Document Conventions](#document-conventions)

---

## About the System

The **HR Management System** is a comprehensive, web-based human resource management solution built on Laravel 12 and Livewire 3. This system is designed to streamline and automate HR processes for organizations of all sizes, from staff registration to payroll management, performance tracking, and contract management.

### Purpose
The system serves as a centralized platform for managing all HR-related activities, including:
- Employee lifecycle management (recruitment to termination)
- Attendance tracking and roster scheduling
- Leave and loan management
- Payroll processing and allowance payments
- Performance evaluation and goal tracking
- Contract management (both staff and institutional)
- Asset management and tracking
- CHOP (Comprehensive Health Operations Planning) budget management
- Audit logging and compliance tracking

### Key Benefits
- **Efficiency**: Automates repetitive HR tasks and reduces manual paperwork
- **Accuracy**: Minimizes human error in payroll calculations and data entry
- **Transparency**: Provides clear audit trails and approval workflows
- **Accessibility**: Web-based access from anywhere with appropriate permissions
- **Real-time Updates**: Livewire-powered reactive interface for instant feedback
- **Scalability**: Designed to grow with your organization
- **Security**: Role-based access control with audit logging

---

## Key Features & Modules

### 1. **Roster Management**
- Create and manage staff work schedules
- Generate rosters for departments and shifts
- Edit and approve roster changes
- Calendar view of staff assignments
- Shift pattern templates

### 2. **Leave Management**
- Employee leave balance tracking
- Online leave request submission with attachments
- Multi-level approval workflow
- Acting assignment during leave periods
- Leave type configuration (annual, sick, maternity, etc.)
- Leave policy enforcement

### 3. **Loan Management**
- Staff loan application and tracking
- Multiple loan types support
- Approval workflow
- Automated payment tracking and deductions
- Loan balance reporting

### 4. **Asset Management**
- Department asset registration
- Asset assignment and tracking
- Asset depreciation calculation
- Asset reports and inventory

### 5. **Human Resources**
- **Staff Registration**: Complete employee records with personal details, qualifications, and documents
- **Staff Management**: Edit profiles, manage dependants, track promotions
- **Attendance Tracking**: Fingerprint device integration for check-in/check-out
- **Contract Requests**: Staff contract issuance and certificate of service

### 6. **Payroll & Staff Payments**
- Automated payroll generation
- Salary scales and grade management
- Allowance and deduction configuration
- Ad-hoc allowance payments
- Payment reports and bank file generation
- Tax calculations

### 7. **Performance Management**
- **My Performance**: Individual goal setting, implementation tracking, and self-evaluation
- **Staff Performance**: Organization-wide performance planning
- Key Performance Indicators (KPIs) by job title
- Department and employee performance plans
- Multi-level evaluation and approval

### 8. **Contract Management**
- **Staff Contracts**: Individual employment contract requests
- **Institutional Contracts**: Organization-wide contract tracking
- Contract expiration notifications
- Contract renewal workflows

### 9. **CHOP Management**
- Budget request submission
- Activity planning and scheduling
- Director review and approval
- Cost analysis and tracking
- Monitoring and evaluation
- Activity reporting

### 10. **Setup & Configuration**
- Location hierarchy (country, region, district, ward, village, street)
- Finance setup (allowances, deductions, salary scales)
- Vendor management
- Approval workflow configuration
- Asset configuration
- Termination reasons
- Backup and recovery

### 11. **User Management & Access Control**
- User account creation and management
- Role-based access control (RBAC)
- Permission categories and assignment
- Two-factor authentication (2FA)
- Password policies

### 12. **Audit Logs**
- Complete audit trail of system activities
- User activity tracking
- Change history for critical data
- Security event logging

---

## System Architecture

### Technology Stack

#### Frontend
- **Framework**: Livewire 3 (full-stack reactive components)
- **Styling**: Bootstrap 5 + Tailwind CSS 4
- **JavaScript Libraries**:
  - ApexCharts (data visualization)
  - FullCalendar (roster and calendar views)
  - Flatpickr (date picker)
  - Choices.js (enhanced select dropdowns)
  - Quill (rich text editor)
  - Dragula (drag-and-drop)
- **Build Tool**: Vite with Hot Module Replacement (HMR)

#### Backend
- **Framework**: Laravel 12 (PHP 8.2+)
- **Database**: MySQL/MariaDB or SQLite
- **Queue System**: Database-backed job queue
- **Authentication**: Laravel Breeze with 2FA support
- **Authorization**: Spatie Laravel Permission (roles & permissions)
- **Activity Logging**: Spatie Laravel Activity Log
- **Notifications**: Laravel Toaster Magic

#### External Integrations
- **Fingerprint Devices**: ZKTeco device integration via Python script
- **API**: RESTful API for external system integration

### Application Structure

```
dasher/
├── app/
│   ├── Livewire/          # Livewire components organized by module
│   │   ├── Hr/            # HR management components
│   │   ├── Setup/         # Configuration components
│   │   ├── Acl/           # Access control components
│   │   ├── Auth/          # Authentication components
│   │   └── Users/         # User profile components
│   ├── Models/            # Eloquent ORM models
│   ├── Services/          # Business logic services
│   └── Http/              # Controllers and middleware
├── database/
│   ├── migrations/        # Database schema definitions
│   └── seeders/           # Initial data seeders
├── resources/
│   ├── views/             # Blade templates
│   │   └── livewire/      # Livewire component views
│   ├── css/               # Stylesheets
│   └── js/                # JavaScript files
├── routes/
│   └── web.php            # Application routes
├── public/                # Public assets
└── storage/               # File uploads and logs
```

### Data Flow
1. **User Request**: User interacts with Livewire component in browser
2. **Livewire Processing**: Component processes request and validates data
3. **Business Logic**: Service classes handle complex operations
4. **Database**: Eloquent models interact with database
5. **Response**: Updated UI rendered and sent back to browser
6. **Activity Log**: Actions recorded in audit trail

---

## User Roles Overview

The system supports a flexible role-based access control system. Below are typical roles and their primary responsibilities:

### 1. **Super Administrator**
- **Access Level**: Complete system access
- **Responsibilities**:
  - System configuration and setup
  - User and role management
  - All module access
  - System maintenance and backup
  - Audit log review

### 2. **HR Manager**
- **Access Level**: Full HR module access
- **Responsibilities**:
  - Staff registration and management
  - Leave policy management
  - Loan policy configuration
  - Payroll generation
  - Contract issuance
  - Performance management oversight
  - HR reports and analytics

### 3. **Department Head**
- **Access Level**: Department-specific access
- **Responsibilities**:
  - Department roster management
  - Leave approvals
  - Performance evaluations
  - Department asset management
  - CHOP budget requests
  - Team management

### 4. **Finance Officer**
- **Access Level**: Finance-related modules
- **Responsibilities**:
  - Payroll approval
  - Allowance payments
  - Financial reports
  - Vendor management
  - CHOP budget review

### 5. **Contract Manager**
- **Access Level**: Contract management modules
- **Responsibilities**:
  - Institutional contract creation
  - Contract approval workflows
  - Contract renewal management
  - Certificate of service issuance

### 6. **Employee (Staff)**
- **Access Level**: Self-service access
- **Responsibilities**:
  - View own profile and records
  - Request leave
  - Apply for loans
  - View personal contract
  - Track performance goals
  - Submit CHOP activity reports (if applicable)

### 7. **Performance Evaluator**
- **Access Level**: Performance module access
- **Responsibilities**:
  - Review employee evaluations
  - Approve performance plans
  - Assign duties and KPIs
  - Track performance implementation

### 8. **Approver (Various Types)**
- **Access Level**: Specific approval workflows
- **Types**:
  - Leave Approver
  - Loan Approver
  - Roster Approver
  - Payroll Approver
  - Contract Approver
  - CHOP Activity Approver

### Permission Categories
The system organizes permissions into categories:
- Roster Management
- Leave Management
- Loan Management
- Asset Management
- Staff Management
- Payroll Management
- Performance Management
- Contract Management
- Approvals
- CHOP Management
- System Settings
- User Management

---

## Document Conventions

### Icons and Symbols
- ✅ **Success/Completed**: Indicates successful operation
- ⚠️ **Warning**: Important information to note
- ❌ **Error**: Operation failed or not allowed
- 💡 **Tip**: Helpful suggestion or best practice
- 📝 **Note**: Additional information
- 🔒 **Security**: Security-related information
- 🔧 **Technical**: Technical details for administrators

### Text Formatting
- **Bold**: Important terms, button names, menu items
- *Italic*: Emphasis or variable values
- `Code format`: File names, commands, code snippets
- > Quoted text: System messages or notifications

### Code Blocks
```bash
# Shell commands appear in code blocks
php artisan migrate
```

```php
// PHP code examples
$user = User::find(1);
```

### Navigation Paths
Menu navigation is indicated with arrows:
- Dashboard → Human Resources → Staff Registration

### Screenshots
Screenshots are provided where necessary, indicated as:
- **[Screenshot: Dashboard Overview]**

### Procedural Steps
Step-by-step instructions are numbered:
1. First step
2. Second step
3. Third step

### Prerequisites
Requirements before performing an action are listed with checkboxes:
- [ ] Requirement 1
- [ ] Requirement 2

### Permission Requirements
Actions requiring specific permissions are noted:
> **Required Permission**: `manage-staff`

### Version Information
This manual is written for:
- **System Version**: 1.0
- **Laravel Version**: 12.x
- **Livewire Version**: 3.x
- **Last Updated**: January 2026

---

## Getting Help

### In-System Help
- Look for the **Help** icon (?) next to complex features
- Hover over fields for tooltips with additional information
- Check the system notifications for important updates

### Contact Support
- **Technical Issues**: Contact your system administrator
- **Feature Requests**: Submit through the feedback form
- **Training**: Request training sessions from HR department

### Additional Resources
- User manual sections for specific modules
- Video tutorials (if available)
- FAQ section (Section 15)
- System administrator contact information

---

## Quick Start Guide

### For Employees
1. Log in with your credentials
2. View your dashboard for overview
3. Request leave: **Dashboard → Leave → Request Leave**
4. Check attendance: View your profile
5. Track performance: **My Performance** menu

### For Managers
1. Log in and review dashboard alerts
2. Approve pending requests: Check approval queues
3. Review team roster: **Roster → Overview**
4. Manage staff: **Human Resources → Staff Registration**
5. Generate reports: Available in each module

### For Administrators
1. Complete initial system setup (Section 2)
2. Create user accounts and assign roles (Section 4)
3. Configure organization structure (Section 3)
4. Set up approval workflows
5. Train end users on system usage

---

**Next Steps**: Proceed to [Section 2: System Installation & Configuration](./02-system-installation-and-configuration.md) for setup instructions.
