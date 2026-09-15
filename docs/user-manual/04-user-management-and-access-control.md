# SECTION 4: USER MANAGEMENT & ACCESS CONTROL

## Table of Contents
- [Overview](#overview)
- [Creating Users](#creating-users)
- [Role Management](#role-management)
- [Permission Management](#permission-management)
- [Two-Factor Authentication (2FA)](#two-factor-authentication-2fa)
- [User Profile Management](#user-profile-management)
- [Best Practices](#best-practices)
- [Common Scenarios](#common-scenarios)

---

## Overview

The HR Management System uses a sophisticated Role-Based Access Control (RBAC) system powered by Spatie Laravel Permission. This system provides:

- **Flexible Role Assignment**: Users can have multiple roles
- **Granular Permissions**: Fine-grained control over system features
- **Permission Categories**: Organized permissions for easier management
- **Audit Trail**: All user actions are logged
- **Two-Factor Authentication**: Enhanced security for sensitive accounts

### Key Concepts

- **User**: An account that can log into the system
- **Role**: A collection of permissions (e.g., "HR Manager", "Staff")
- **Permission**: A specific action a user can perform (e.g., "view-staff", "approve-leave")
- **Permission Category**: A logical grouping of related permissions

---

## Creating Users

### Prerequisites
- [ ] You must have the `manage-users` permission
- [ ] Employee record should exist (optional but recommended)

### Navigation
Dashboard → **Users & Access** → **Users**

### Step-by-Step: Creating a New User

#### Step 1: Access User Management

1. Log in as an administrator
2. Navigate to **Users & Access** → **Users**
3. Click the **"Add New User"** button

**[Screenshot: User Management Dashboard]**

#### Step 2: Enter User Information

Fill in the required fields:

| Field | Description | Required | Notes |
|-------|-------------|----------|-------|
| **Name** | Full name of the user | Yes | Display name in the system |
| **Email** | Email address | Yes | Must be unique, used for login |
| **Password** | Initial password | Yes | Minimum 8 characters |
| **Confirm Password** | Repeat password | Yes | Must match password field |
| **Employee** | Link to employee record | No | Recommended for staff members |
| **Status** | Account status | Yes | Active or Inactive |
| **Email Verified** | Mark email as verified | No | Skip email verification |

**Example:**
```
Name: John Doe
Email: john.doe@organization.com
Password: SecurePass123!
Employee: [Select from dropdown]
Status: Active
```

💡 **Tip**: Use strong passwords with at least 8 characters, including uppercase, lowercase, numbers, and special characters.

#### Step 3: Assign Roles

In the **Roles** section:
1. Select one or more roles from the available roles list
2. Common roles:
   - **admin**: Full system access (Super Administrator)
   - **hr-manager**: HR department access
   - **department-head**: Department-specific access
   - **staff**: Basic employee access
   - **finance-officer**: Finance-related access

**Multiple Role Selection:**
- Hold `Ctrl` (Windows/Linux) or `Cmd` (Mac) to select multiple roles
- Users inherit all permissions from assigned roles

#### Step 4: Assign Direct Permissions (Optional)

If needed, assign specific permissions that aren't covered by roles:
1. Scroll to **Additional Permissions** section
2. Check individual permissions
3. These permissions are added to role-based permissions

⚠️ **Warning**: Direct permission assignment should be used sparingly. Prefer role-based permissions for easier management.

#### Step 5: Save User

1. Review all information
2. Click **"Create User"** button
3. Confirmation message appears

**Expected Success Message:**
```
✅ User created successfully! Welcome email sent to john.doe@organization.com
```

### Post-Creation Actions

After creating a user:
- [ ] Verify user appears in user list
- [ ] Test login with provided credentials
- [ ] Instruct user to change password on first login
- [ ] Link to employee record if not done during creation
- [ ] Enable 2FA for sensitive accounts

---

## Role Management

### Understanding Roles

Roles are collections of permissions that define what users can do in the system. Using roles instead of individual permissions simplifies management.

### Default System Roles

| Role | Description | Typical Users |
|------|-------------|---------------|
| **admin** | Super Administrator - Full system access | System administrators, IT staff |
| **hr-manager** | Complete HR management access | HR department heads |
| **department-head** | Department-level management | Department managers |
| **staff** | Basic employee self-service | Regular employees |
| **finance-officer** | Finance and payroll access | Finance department staff |
| **performance-evaluator** | Performance management access | Supervisors, managers |
| **contract-manager** | Contract management access | Contract officers |

### Navigation
Dashboard → **Users & Access** → **Roles**

### Creating a Custom Role

#### Step 1: Access Role Management

1. Navigate to **Users & Access** → **Roles**
2. Click **"Create New Role"** button

#### Step 2: Enter Role Information

| Field | Description | Required |
|-------|-------------|----------|
| **Role Name** | Internal role identifier | Yes |
| **Display Name** | Friendly name shown to users | Yes |
| **Description** | Purpose and scope of role | No |
| **Guard Name** | Authentication guard | Yes (Default: web) |

**Example:**
```
Role Name: regional-manager
Display Name: Regional Manager
Description: Manages multiple departments across regions
Guard Name: web
```

**Naming Conventions:**
- Use lowercase with hyphens (kebab-case)
- Be descriptive but concise
- Examples: `payroll-officer`, `asset-manager`, `audit-reviewer`

#### Step 3: Assign Permissions to Role

Permissions are organized by categories. Select relevant permissions for the role:

**Roster Management**
- [ ] view-roster
- [ ] create-roster
- [ ] edit-roster
- [ ] manage-roster

**Leave Management**
- [ ] view-leave
- [ ] request-leave
- [ ] approve-leave
- [ ] manage-leave

**Loan Management**
- [ ] view-loan
- [ ] request-loan
- [ ] approve-loan
- [ ] manage-loan

**Asset Management**
- [ ] manage-assets

**Staff Management**
- [ ] view-staff
- [ ] manage-staff
- [ ] view-attendance

**Payroll Management**
- [ ] view-payroll
- [ ] generate-payroll
- [ ] manage-payroll

**Performance Management**
- [ ] view-performance
- [ ] manage-performance

**Contract Management**
- [ ] view-contracts
- [ ] manage-contracts

**Approvals**
- [ ] approve-requests
- [ ] approve-payroll
- [ ] approve-roster
- [ ] approve-allowances
- [ ] approve-chop
- [ ] approve-contract-requests

**CHOP Management**
- [ ] view-chop
- [ ] manage-chop

**System Settings**
- [ ] manage-settings
- [ ] view-audit-logs
- [ ] manage-audit-logs
- [ ] manage-backups

**User Management**
- [ ] manage-users
- [ ] manage-roles
- [ ] manage-permissions

💡 **Tip**: Use "Select All in Category" checkbox to quickly select all permissions within a category.

#### Step 4: Save Role

1. Review selected permissions
2. Click **"Create Role"** button
3. Role is now available for user assignment

### Editing Existing Roles

1. Navigate to **Users & Access** → **Roles**
2. Find the role to edit
3. Click **"Edit"** button
4. Modify permissions as needed
5. Click **"Update Role"**

⚠️ **Warning**: Changes to role permissions immediately affect all users with that role.

### Deleting Roles

1. Navigate to **Users & Access** → **Roles**
2. Find the role to delete
3. Click **"Delete"** button
4. Confirm deletion

🔒 **Security Note**: Cannot delete roles assigned to active users. Reassign users first.

### Viewing Role Members

To see which users have a specific role:
1. Navigate to **Users & Access** → **Roles**
2. Click on role name
3. View **"Users with this role"** section

---

## Permission Management

### Understanding Permissions

Permissions are the atomic units of access control. Each permission represents a specific action users can perform.

### Permission Categories

The system organizes permissions into 12 categories:

#### 1. Roster Management
- `view-roster`: View roster schedules and overview
- `create-roster`: Create and generate new rosters
- `edit-roster`: Edit existing roster schedules
- `manage-roster`: Manage and delete roster schedules

#### 2. Leave Management
- `view-leave`: View leave balances and history
- `request-leave`: Submit leave requests
- `approve-leave`: Approve or reject leave requests
- `manage-leave`: Manage leave types and policies

#### 3. Loan Management
- `view-loan`: View loan balances and history
- `request-loan`: Submit loan requests
- `approve-loan`: Approve or reject loan requests
- `manage-loan`: Manage loan types and policies

#### 4. Asset Management
- `manage-assets`: Manage department assets and asset reports

#### 5. Staff Management
- `view-staff`: View staff list and details
- `manage-staff`: Add, edit, and manage staff records
- `view-attendance`: View attendance records and fingerprint data

#### 6. Payroll Management
- `view-payroll`: View payroll records and reports
- `generate-payroll`: Generate payroll for staff
- `manage-payroll`: Manage allowances, deductions, and payments

#### 7. Performance Management
- `view-performance`: View performance plans and reports
- `manage-performance`: Manage KPIs, duties, and performance evaluations

#### 8. Contract Management
- `view-contracts`: View institutional contracts
- `manage-contracts`: Create, edit, and manage contracts

#### 9. Approvals
- `approve-requests`: General approval access
- `approve-payroll`: Approve or reject payroll requests
- `approve-roster`: Approve or reject roster schedules
- `approve-allowances`: Approve or reject allowance requests
- `approve-chop`: Approve or reject CHOP activities
- `approve-contract-requests`: Approve or reject staff contract requests

#### 10. CHOP Management
- `view-chop`: View CHOP budget requests and activity reports
- `manage-chop`: Manage CHOP activities, reviews, and settings

#### 11. System Settings
- `manage-settings`: Manage system settings, locations, and configurations
- `view-audit-logs`: View audit logs
- `manage-audit-logs`: View and manage audit logs
- `manage-backups`: View and manage backups

#### 12. User Management
- `manage-users`: Create, edit, and manage user accounts
- `manage-roles`: Create and manage user roles
- `manage-permissions`: Create and manage permissions

### Navigation
Dashboard → **Users & Access** → **Permissions**

### Viewing Permissions

1. Navigate to **Users & Access** → **Permissions**
2. View permissions organized by category
3. See which roles have each permission
4. Click on permission to view details

### Creating Custom Permissions

> **Required Permission**: `manage-permissions`

#### Step 1: Access Permission Management

1. Navigate to **Users & Access** → **Permissions**
2. Click **"Create New Permission"** button

#### Step 2: Enter Permission Details

| Field | Description | Required |
|-------|-------------|----------|
| **Permission Name** | Internal identifier | Yes |
| **Description** | What this permission allows | Yes |
| **Category** | Permission category | Yes |
| **Guard Name** | Authentication guard | Yes (Default: web) |

**Example:**
```
Permission Name: export-payroll-reports
Description: Export payroll reports to Excel/PDF
Category: Payroll Management
Guard Name: web
```

**Naming Conventions:**
- Use lowercase with hyphens
- Start with action verb: view, create, edit, manage, approve, export
- Be specific: `approve-leave` not just `approve`

#### Step 3: Assign to Roles (Optional)

Select roles that should have this permission:
- [ ] admin
- [ ] hr-manager
- [ ] finance-officer

#### Step 4: Save Permission

1. Review information
2. Click **"Create Permission"**
3. Permission is now available for role assignment

### Editing Permissions

1. Navigate to **Users & Access** → **Permissions**
2. Find permission to edit
3. Click **"Edit"** button
4. Update description or category
5. Click **"Update Permission"**

⚠️ **Note**: Permission name cannot be changed after creation to prevent breaking role assignments.

---

## Two-Factor Authentication (2FA)

### Overview

Two-Factor Authentication adds an extra layer of security by requiring a second verification method beyond passwords.

### Enabling 2FA for Your Account

#### Step 1: Access Profile Settings

1. Click your name in top-right corner
2. Select **"Profile Settings"**
3. Navigate to **"Security"** tab

#### Step 2: Enable 2FA

1. Click **"Enable Two-Factor Authentication"** button
2. System generates QR code
3. Open authenticator app on your phone:
   - Google Authenticator
   - Microsoft Authenticator
   - Authy
   - Any TOTP-compatible app

#### Step 3: Scan QR Code

1. Open authenticator app
2. Tap **"Add Account"** or **"+"**
3. Scan QR code displayed on screen
4. App generates 6-digit code

#### Step 4: Verify Setup

1. Enter 6-digit code from authenticator app
2. Click **"Verify and Enable"**
3. Save recovery codes in secure location

**Recovery Codes:**
```
1. ABCD-EFGH-IJKL
2. MNOP-QRST-UVWX
3. YZAB-CDEF-GHIJ
...
```

🔒 **Security Note**: Store recovery codes securely. They are needed if you lose access to your authenticator app.

### Logging In with 2FA

1. Enter email and password as usual
2. Click **"Login"**
3. Enter 6-digit code from authenticator app
4. Click **"Verify"**

### Using Recovery Codes

If you lose your phone or authenticator app:
1. Enter email and password
2. Click **"Use Recovery Code"** link
3. Enter one of your recovery codes
4. Click **"Verify"**

⚠️ **Warning**: Each recovery code can only be used once.

### Disabling 2FA

1. Navigate to **Profile Settings** → **Security**
2. Click **"Disable Two-Factor Authentication"**
3. Enter 6-digit code to confirm
4. Click **"Disable"**

### Enforcing 2FA for All Users (Administrators)

As an administrator, you can require 2FA for specific roles:

1. Navigate to **Setup & Config** → **Setup**
2. Go to **"Security Settings"**
3. Enable **"Require 2FA for Administrators"**
4. Select roles that must use 2FA
5. Click **"Save Settings"**

---

## User Profile Management

### Viewing Your Profile

1. Click your name in top-right corner
2. Select **"My Profile"**

**Profile Information Displayed:**
- Personal details
- Contact information
- Linked employee record
- Assigned roles and permissions
- Recent activity
- Login history

### Updating Your Profile

#### Step 1: Edit Profile Information

1. Navigate to **My Profile**
2. Click **"Edit Profile"** button
3. Update fields:
   - Name
   - Email
   - Phone number
   - Profile picture

#### Step 2: Save Changes

1. Click **"Update Profile"**
2. Confirmation message appears

### Changing Your Password

#### Step 1: Access Password Change

1. Navigate to **My Profile** → **Security**
2. Click **"Change Password"**

#### Step 2: Enter Passwords

| Field | Description |
|-------|-------------|
| **Current Password** | Your existing password |
| **New Password** | New password (min 8 characters) |
| **Confirm New Password** | Repeat new password |

#### Step 3: Save New Password

1. Click **"Change Password"**
2. Log out and log back in with new password

**Password Requirements:**
- Minimum 8 characters
- At least one uppercase letter
- At least one lowercase letter
- At least one number
- At least one special character (recommended)

### Viewing Your Permissions

To see what you can do in the system:
1. Navigate to **My Profile** → **Permissions**
2. View all assigned permissions
3. Permissions organized by category
4. Source shown (from role or direct assignment)

---

## Best Practices

### User Management Best Practices

1. **Use Roles, Not Direct Permissions**
   - Assign users to roles instead of individual permissions
   - Easier to manage as organization grows
   - More consistent access control

2. **Principle of Least Privilege**
   - Give users only the permissions they need
   - Don't assign admin role unless necessary
   - Review permissions regularly

3. **Regular Access Reviews**
   - Quarterly review of user accounts
   - Remove inactive users
   - Update permissions for role changes

4. **Strong Password Policy**
   - Enforce minimum 8 characters
   - Require password changes every 90 days (optional)
   - Use 2FA for sensitive accounts

5. **Link Users to Employees**
   - Always link user accounts to employee records
   - Enables proper tracking and reporting
   - Required for many HR features

6. **Document Role Definitions**
   - Maintain clear descriptions for each role
   - Document permission rationale
   - Keep role documentation updated

### Security Best Practices

1. **Enable 2FA for:**
   - All administrators
   - HR managers
   - Finance officers
   - Anyone with approval permissions

2. **Monitor Audit Logs**
   - Review audit logs regularly
   - Investigate suspicious activity
   - Track sensitive operations

3. **Secure Admin Accounts**
   - Use strong, unique passwords
   - Enable 2FA mandatory
   - Limit number of admin accounts

4. **Onboarding Checklist**
   - [ ] Create user account
   - [ ] Assign appropriate role
   - [ ] Link to employee record
   - [ ] Send welcome email with temporary password
   - [ ] Require password change on first login
   - [ ] Enable 2FA if required
   - [ ] Provide system training

5. **Offboarding Checklist**
   - [ ] Disable user account immediately
   - [ ] Remove all role assignments
   - [ ] Revoke API access (if applicable)
   - [ ] Document reason for termination
   - [ ] Archive user data if required
   - [ ] Remove from all approval workflows

---

## Common Scenarios

### Scenario 1: New Employee Joins

**Objective**: Create account for new HR assistant

**Steps:**
1. Create user account with email: `newhr@organization.com`
2. Assign role: `hr-manager` (if full access) or create custom role
3. Link to employee record (created in HR module)
4. Send welcome email with temporary password
5. User logs in and changes password
6. User sets up 2FA (if required)

**Permissions Needed:**
- `view-staff`, `manage-staff`
- `view-leave`, `approve-leave`
- `view-payroll`

### Scenario 2: Promoting Employee to Department Head

**Objective**: Grant department management access

**Steps:**
1. Navigate to **Users & Access** → **Users**
2. Find user account
3. Click **"Edit"**
4. Add role: `department-head`
5. Keep existing `staff` role (for self-service features)
6. Save changes
7. Notify user of new permissions

**New Capabilities:**
- Approve leave for department staff
- Manage department roster
- View department performance
- Manage department assets

### Scenario 3: Temporary Approval Access

**Objective**: Grant temporary leave approval while manager is on leave

**Option A: Using Acting Assignment (Recommended)**
- Use the Leave Management acting assignment feature
- Automatically grants temporary permissions
- Auto-reverts when manager returns

**Option B: Manual Permission Assignment**
1. Edit user account
2. Add direct permission: `approve-leave`
3. Document temporary nature in notes
4. Set calendar reminder to remove
5. Remove permission when no longer needed

### Scenario 4: Creating Specialized Role

**Objective**: Create role for "Payroll Officer"

**Steps:**
1. Navigate to **Users & Access** → **Roles**
2. Click **"Create New Role"**
3. Enter details:
   ```
   Role Name: payroll-officer
   Display Name: Payroll Officer
   Description: Processes payroll and manages allowances
   ```
4. Assign permissions:
   - `view-staff`
   - `view-payroll`
   - `generate-payroll`
   - `manage-payroll`
   - `view-attendance`
   - `approve-allowances`
5. Save role
6. Assign to appropriate users

### Scenario 5: User Forgot Password

**Steps:**
1. User clicks **"Forgot Password"** on login page
2. Enters email address
3. Receives password reset email
4. Clicks link in email
5. Sets new password
6. Logs in with new password

**If Email Not Working (Admin Reset):**
1. Admin navigates to **Users & Access** → **Users**
2. Find user account
3. Click **"Reset Password"**
4. Generate temporary password
5. Provide to user securely (not via email)
6. User changes password on first login

### Scenario 6: Bulk User Creation

**For Multiple New Users:**

**Option A: Using Seeder (Technical)**
```bash
php artisan tinker
$users = [
    ['name' => 'User 1', 'email' => 'user1@org.com', 'role' => 'staff'],
    ['name' => 'User 2', 'email' => 'user2@org.com', 'role' => 'staff'],
];
foreach ($users as $userData) {
    $user = User::create([
        'name' => $userData['name'],
        'email' => $userData['email'],
        'password' => Hash::make('TempPass123!'),
    ]);
    $user->assignRole($userData['role']);
}
exit
```

**Option B: Manual Creation**
1. Create spreadsheet with user details
2. Create users one by one
3. Use consistent temporary password
4. Send welcome emails in batch
5. Users change passwords on first login

---

## Troubleshooting

### Issue: User Can't See Expected Menu Items

**Cause**: Missing permissions or role not assigned

**Solution:**
1. Verify user roles: **Users & Access** → **Users** → [User] → **Edit**
2. Check role permissions: **Users & Access** → **Roles** → [Role]
3. Verify menu requires specific permission (check with administrator)
4. Assign missing role or permission
5. User logs out and back in

### Issue: "Access Denied" Error

**Cause**: User lacks required permission for that action

**Solution:**
1. Note which action was being attempted
2. Check required permission (usually shown in error message)
3. Verify user has that permission
4. If needed, update role or assign direct permission
5. User refreshes page

### Issue: 2FA Not Working

**Cause**: Time sync issue or wrong app

**Solution:**
1. Verify phone time is set to automatic
2. Check correct account in authenticator app
3. Try next 6-digit code (codes refresh every 30 seconds)
4. If still failing, use recovery code
5. Disable and re-enable 2FA if necessary

### Issue: Multiple Users with Same Email

**Prevention**: System prevents duplicate emails

**If Needed:**
1. Use email aliases: `user+1@org.com`, `user+2@org.com`
2. Or use different domain: `user@org.com` vs `user@org.co.tz`
3. Best practice: One email per user account

---

**Next Steps**:
- Explore [Section 5: HR Module User Guide](./05-hr-module-user-guide.md) for staff management
- Review [Section 7: Leave Management](./07-leave-management.md) for approval workflows
- See [Section 14: Audit Logs](./14-audit-logs-and-system-monitoring.md) for user activity tracking
