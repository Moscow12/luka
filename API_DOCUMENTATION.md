# Employee Registration API Documentation

## Base URL
```
http://your-domain.com/api
```

## Authentication
Currently, the API endpoints do not require authentication. If authentication is needed in the future, use Laravel Sanctum tokens.

---

## Employee Registration Endpoint

### POST `/api/employees/register`

Register a new employee in the system with automatic duplicate checking and intelligent field resolution.

### Request Headers
```
Content-Type: application/json
Accept: application/json
```

### Request Body

#### Required Fields

| Field | Type | Validation | Description |
|-------|------|------------|-------------|
| `first_name` | string | max:100 | Employee's first name |
| `last_name` | string | max:100 | Employee's last name |
| `gender` | string | in:Male,Female,Other | Employee's gender |
| `dob` | date | before:today | Date of birth (YYYY-MM-DD) |
| `phone` | string | max:20 | Phone number (used for duplicate check) |
| `email` | email | unique | Email address |
| `employment_type` | string | in:Full-time,Part-time,Contract,Temporary | Type of employment |
| `hired_date` | date | | Date of hiring (YYYY-MM-DD) |
| `education_level` | string | in:Primary,Diploma,Certificate,Degree,Masters,PhD | Highest education level |
| `marital_status` | string | in:Single,Married,Divorced,Widowed,Separated,Never married,Not applicable | Marital status |
| `department` | string | | Department name (searches database) |
| `title` | string | | Job title name (searches database) |
| `designation` | string | | Designation name (searches database) |
| `workstation` | string | | Workstation name (searches database) |
| `denomination` | string | | Religious denomination name (searches database) |

#### Optional Fields

| Field | Type | Validation | Description |
|-------|------|------------|-------------|
| `middle_name` | string | max:100 | Employee's middle name |
| `national_id` | string | max:50, unique | National ID number |
| `employee_no` | string | max:50, unique | Employee number (auto-generated if not provided) |
| `district` | string | | District name for location |
| `tin_number` | string | max:50 | Tax Identification Number |
| `fpid` | string | max:50 | Fingerprint ID |
| `photo` | string | | Path or URL to photo |
| `signature` | string | | Path or URL to signature |
| `added_by` | uuid | | ID of user adding the employee |

### How It Works

#### 1. Duplicate Detection
The API automatically checks if an employee already exists by:
- Phone number
- Employee number

If a match is found, the existing employee data is returned instead of creating a duplicate.

#### 2. Intelligent Field Resolution
Foreign key fields are resolved automatically by searching the database:
- **Department**: Searches `departments` table by `name` (LIKE search)
- **Title**: Searches `jobtitles` table by `name`
- **Designation**: Searches `designations` table by `name`
- **Workstation**: Searches `workstations` table by `workstation_name`
- **Denomination**: Searches `denominations` table by `name`

#### 3. Automatic Location Assignment
Location fields are automatically populated:
- **Country**: Defaults to Tanzania (or first country in database)
- **Region**: First region in the selected country
- **District**:
  - If `district` name provided → searches by name
  - Otherwise → first district in the region
- **Ward**: First ward in the selected district

#### 4. Employee Number Generation
If `employee_no` is not provided, it's auto-generated in the format:
```
STIH/YYYY/XXX
```
Example: `STIH/2026/001`

### Example Request

```json
{
  "first_name": "John",
  "middle_name": "Michael",
  "last_name": "Doe",
  "gender": "Male",
  "dob": "1990-05-15",
  "phone": "0712345678",
  "email": "john.doe@example.com",
  "employment_type": "Full-time",
  "hired_date": "2026-01-15",
  "education_level": "Degree",
  "marital_status": "Single",
  "department": "Human Resources",
  "title": "HR Manager",
  "designation": "Senior Officer",
  "workstation": "Head Office",
  "denomination": "Catholic",
  "district": "Dar es Salaam",
  "national_id": "19900515-12345-67890",
  "tin_number": "123-456-789"
}
```

### Success Response (201 Created)

```json
{
  "success": true,
  "message": "Employee registered successfully",
  "data": {
    "employee": {
      "id": "9d3e4f5a-6b7c-8d9e-0f1a-2b3c4d5e6f7a",
      "employee_no": "STIH/2026/001",
      "first_name": "John",
      "middle_name": "Michael",
      "last_name": "Doe",
      "gender": "Male",
      "dob": "1990-05-15",
      "phone": "0712345678",
      "email": "john.doe@example.com",
      "employment_type": "Full-time",
      "hired_date": "2026-01-15",
      "status": "active",
      "education_level": "Degree",
      "marital_status": "Single",
      "created_at": "2026-05-14T10:30:00.000000Z",
      "updated_at": "2026-05-14T10:30:00.000000Z",
      "department": {
        "id": "uuid",
        "name": "Human Resources"
      },
      "designation": {
        "id": "uuid",
        "name": "Senior Officer"
      },
      "workstation": {
        "id": "uuid",
        "workstation_name": "Head Office"
      }
    }
  }
}
```

### Employee Already Exists Response (200 OK)

```json
{
  "success": false,
  "message": "Employee already exists",
  "data": {
    "employee": {
      "id": "existing-uuid",
      "employee_no": "STIH/2025/123",
      "first_name": "John",
      "last_name": "Doe",
      "phone": "0712345678",
      "email": "john.doe@example.com",
      "status": "active",
      "department": {
        "id": "uuid",
        "name": "Human Resources"
      },
      "designation": {
        "id": "uuid",
        "name": "Senior Officer"
      },
      "workstation": {
        "id": "uuid",
        "workstation_name": "Head Office"
      }
    }
  }
}
```

### Validation Error Response (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "first_name": [
      "The first name field is required."
    ],
    "email": [
      "The email has already been taken."
    ],
    "gender": [
      "The selected gender is invalid."
    ]
  }
}
```

### Field Resolution Error Response (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "Failed to resolve required fields",
  "errors": {
    "department": "Department not found",
    "title": "Job title not found",
    "designation": null,
    "workstation": null,
    "denomination": null
  }
}
```

### Server Error Response (500 Internal Server Error)

```json
{
  "success": false,
  "message": "Failed to register employee",
  "error": "Error details here"
}
```

---

## Additional Employee Endpoints

### GET `/api/employees`

Get a paginated list of all active employees.

#### Response (200 OK)
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": "uuid",
        "employee_no": "STIH/2026/001",
        "first_name": "John",
        "last_name": "Doe",
        "email": "john.doe@example.com",
        "phone": "0712345678",
        "status": "active",
        "department": {...},
        "designation": {...},
        "workstation": {...}
      }
    ],
    "per_page": 50,
    "total": 100
  }
}
```

### GET `/api/employees/{id}`

Get details of a specific employee.

#### Parameters
- `id` (uuid) - Employee ID

#### Response (200 OK)
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "employee_no": "STIH/2026/001",
    "first_name": "John",
    "middle_name": "Michael",
    "last_name": "Doe",
    "gender": "Male",
    "dob": "1990-05-15",
    "phone": "0712345678",
    "email": "john.doe@example.com",
    "status": "active",
    "department": {...},
    "designation": {...},
    "workstation": {...},
    "position": {...},
    "country": {...},
    "region": {...},
    "district": {...},
    "ward": {...}
  }
}
```

#### Error Response (404 Not Found)
```json
{
  "success": false,
  "message": "Employee not found",
  "error": "No query results for model [App\\Models\\Employee] uuid"
}
```

---

## Testing with cURL

### Register New Employee
```bash
curl -X POST http://your-domain.com/api/employees/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Doe",
    "gender": "Male",
    "dob": "1990-05-15",
    "phone": "0712345678",
    "email": "john.doe@example.com",
    "employment_type": "Full-time",
    "hired_date": "2026-01-15",
    "education_level": "Degree",
    "marital_status": "Single",
    "department": "Human Resources",
    "title": "HR Manager",
    "designation": "Senior Officer",
    "workstation": "Head Office",
    "denomination": "Catholic"
  }'
```

### Get All Employees
```bash
curl -X GET http://your-domain.com/api/employees \
  -H "Accept: application/json"
```

### Get Specific Employee
```bash
curl -X GET http://your-domain.com/api/employees/{employee-id} \
  -H "Accept: application/json"
```

---

## Testing with Postman

### 1. Register Employee
- **Method**: POST
- **URL**: `http://your-domain.com/api/employees/register`
- **Headers**:
  - `Content-Type: application/json`
  - `Accept: application/json`
- **Body** (raw JSON): Use example request above

### 2. Get Employees List
- **Method**: GET
- **URL**: `http://your-domain.com/api/employees`
- **Headers**:
  - `Accept: application/json`

### 3. Get Single Employee
- **Method**: GET
- **URL**: `http://your-domain.com/api/employees/{id}`
- **Headers**:
  - `Accept: application/json`

---

## Error Codes Summary

| HTTP Code | Meaning | When It Occurs |
|-----------|---------|----------------|
| 200 | OK | Employee already exists (duplicate found) |
| 201 | Created | Employee successfully registered |
| 404 | Not Found | Employee ID not found (GET request) |
| 422 | Unprocessable Entity | Validation failed or field resolution failed |
| 500 | Internal Server Error | Server error during processing |

---

## Notes

1. **Duplicate Prevention**: The API automatically prevents duplicate entries based on phone number or employee number.

2. **Fuzzy Search**: All name-based searches use LIKE queries, so partial matches work:
   - "HR" matches "Human Resources"
   - "Manager" matches "HR Manager"

3. **Case Insensitive**: All searches are case-insensitive.

4. **Auto-Generated Fields**:
   - Employee number is auto-generated if not provided
   - Location fields (country, region, district, ward) are automatically assigned

5. **Default Status**: All new employees are created with `status: 'active'`

6. **Transaction Safety**: All database operations are wrapped in transactions and rolled back on error.

---

## Support

For issues or questions, please contact the development team or create an issue in the project repository.
