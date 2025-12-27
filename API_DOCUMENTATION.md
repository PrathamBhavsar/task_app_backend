# Interior Design API Documentation

## Base URL
```
http://localhost:3000/api/v1
```

## Authentication

### Overview
The API uses JWT (JSON Web Token) authentication with access and refresh tokens:
- **Access Token**: Short-lived token (expires in 15 minutes) for API requests
- **Refresh Token**: Long-lived token (expires in 7 days) to get new access tokens
- **Auto-refresh**: Frontend should automatically refresh tokens when access token expires

### Headers
For authenticated requests, include:
```
Authorization: Bearer <access_token>
```

---

## Authentication Endpoints

### 1. Login
**POST** `/auth/login`

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Success Response (200):**
```json
{
  "status": "success",
  "data": {
    "accessToken": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "refreshToken": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expiresIn": 900,
    "user": {
      "userId": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "role": "admin"
    }
  },
  "message": "Login successful"
}
```

**Error Response (401):**
```json
{
  "message": "Invalid credentials"
}
```

### 2. Register
**POST** `/auth/register`

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "user@example.com",
  "password": "password123",
  "contactNo": "+1234567890",
  "address": "123 Main St",
  "role": "salesperson"
}
```

**Success Response (201):**
```json
{
  "status": "success",
  "data": {
    "accessToken": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "refreshToken": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expiresIn": 900,
    "user": {
      "userId": 2,
      "name": "John Doe",
      "email": "user@example.com",
      "role": "salesperson"
    }
  },
  "message": "Registration successful"
}
```

### 3. Refresh Token
**POST** `/auth/refresh`

**Request Body:**
```json
{
  "refreshToken": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

**Success Response (200):**
```json
{
  "status": "success",
  "data": {
    "accessToken": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "refreshToken": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expiresIn": 900
  },
  "message": "Token refreshed"
}
```

### 4. Logout
**POST** `/auth/logout`

**Headers:** `Authorization: Bearer <access_token>`

**Success Response (200):**
```json
{
  "status": "success",
  "data": null,
  "message": "Logged out successfully"
}
```

---

## Auto-Login Implementation Guide

### Frontend Token Management
```javascript
// Store tokens
localStorage.setItem('accessToken', data.accessToken);
localStorage.setItem('refreshToken', data.refreshToken);

// Axios interceptor for auto-refresh
axios.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      const refreshToken = localStorage.getItem('refreshToken');
      if (refreshToken) {
        try {
          const response = await axios.post('/auth/refresh', { refreshToken });
          const { accessToken } = response.data.data;
          localStorage.setItem('accessToken', accessToken);
          
          // Retry original request
          error.config.headers.Authorization = `Bearer ${accessToken}`;
          return axios.request(error.config);
        } catch (refreshError) {
          // Refresh failed, redirect to login
          localStorage.clear();
          window.location.href = '/login';
        }
      }
    }
    return Promise.reject(error);
  }
);
```

---

## Dashboard Analytics

### Get Dashboard Overview
**GET** `/dashboard/overview`

**Headers:** `Authorization: Bearer <access_token>`

**Success Response (200):**
```json
{
  "status": "success",
  "data": {
    "totalTasks": 20,
    "completedTasks": 3,
    "inProgressTasks": 17,
    "tasksDue": 19,
    "tasksByStage": {
      "customerSelection": 0,
      "quotation": 0,
      "measurement": 0,
      "advancePaymentSO": 0,
      "finalPaymentInvoice": 0
    },
    "tasksByReference": [
      {
        "type": "designer",
        "name": "Rahul Designs",
        "count": 1
      },
      {
        "type": "designer", 
        "name": "Arjun Associates",
        "count": 1
      },
      {
        "type": "designer",
        "name": "Pooja Design Lab", 
        "count": 1
      },
      {
        "type": "designer",
        "name": "Meera Interiors",
        "count": 0
      },
      {
        "type": "designer",
        "name": "Nisha Creative",
        "count": 0
      },
      {
        "type": "direct",
        "name": "Direct Customer",
        "count": 0
      }
    ],
    "designerPerformance": [
      {
        "designerId": 1,
        "name": "Rahul Designs",
        "completedTasks": 1,
        "totalTasks": 3
      },
      {
        "designerId": 3,
        "name": "Arjun Associates", 
        "completedTasks": 1,
        "totalTasks": 2
      },
      {
        "designerId": 6,
        "name": "Pooja Design Lab",
        "completedTasks": 1,
        "totalTasks": 1
      },
      {
        "designerId": 2,
        "name": "Meera Interiors",
        "completedTasks": 0,
        "totalTasks": 2
      },
      {
        "designerId": 4,
        "name": "Nisha Creative",
        "completedTasks": 0,
        "totalTasks": 3
      }
    ],
    "salespersonWorkload": [
      {
        "userId": 4,
        "name": "John Sales",
        "taskCount": 6
      },
      {
        "userId": 5,
        "name": "Sarah Sales", 
        "taskCount": 5
      },
      {
        "userId": 6,
        "name": "Mike Sales",
        "taskCount": 5
      },
      {
        "userId": 7,
        "name": "Emma Sales",
        "taskCount": 4
      }
    ]
  },
  "message": "Dashboard overview retrieved"
}
```

**Description:**
- **totalTasks**: Total number of tasks in the system
- **completedTasks**: Number of completed tasks
- **inProgressTasks**: Number of tasks currently in progress
- **tasksDue**: Number of tasks that are overdue (past due date and not completed/cancelled)
- **tasksByStage**: Breakdown of tasks by workflow stage
  - `customerSelection`: Tasks in "Created" status
  - `quotation`: Tasks in "Quote: Done" status  
  - `measurement`: Tasks in "Measurement: Done" status
  - `advancePaymentSO`: Tasks in "Approved" status
  - `finalPaymentInvoice`: Tasks in "Completed" status
- **tasksByReference**: Task distribution by reference source (designers vs direct customers)
- **designerPerformance**: Performance metrics for each designer (completed vs total tasks)
- **salespersonWorkload**: Task distribution among active salespersons

---

## Users Management

### 1. Get All Users
**GET** `/users?limit=10&offset=0`

**Success Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "userId": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "contactNo": "+1234567890",
      "address": "123 Main St",
      "role": "admin",
      "profileBgColor": "#FF5733",
      "isActive": true,
      "createdAt": "2024-01-01T00:00:00.000Z",
      "lastLoginAt": "2024-01-15T10:30:00.000Z"
    }
  ],
  "pagination": {
    "total": 25,
    "limit": 10,
    "offset": 0,
    "hasNext": true,
    "hasPrev": false
  },
  "message": "Users retrieved"
}
```

### 2. Get User by ID
**GET** `/users/{id}`

**Success Response (200):**
```json
{
  "status": "success",
  "data": {
    "userId": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "contactNo": "+1234567890",
    "address": "123 Main St",
    "role": "admin",
    "profileBgColor": "#FF5733",
    "isActive": true,
    "createdAt": "2024-01-01T00:00:00.000Z",
    "lastLoginAt": "2024-01-15T10:30:00.000Z"
  },
  "message": "User retrieved"
}
```

### 3. Create User
**POST** `/users`

**Request Body:**
```json
{
  "name": "Jane Smith",
  "email": "jane@example.com",
  "password": "password123",
  "contactNo": "+1234567891",
  "address": "456 Oak Ave",
  "role": "salesperson",
  "profileBgColor": "#33FF57",
  "isActive": true
}
```

**Success Response (201):**
```json
{
  "status": "success",
  "data": {
    "userId": 2,
    "name": "Jane Smith",
    "email": "jane@example.com",
    "contactNo": "+1234567891",
    "address": "456 Oak Ave",
    "role": "salesperson",
    "profileBgColor": "#33FF57",
    "isActive": true,
    "createdAt": "2024-01-15T12:00:00.000Z",
    "lastLoginAt": null
  },
  "message": "User created"
}
```

### 4. Update User
**PATCH** `/users/{id}`

**Request Body:**
```json
{
  "name": "Jane Smith Updated",
  "contactNo": "+1234567892",
  "isActive": false
}
```

**Success Response (200):**
```json
{
  "status": "success",
  "data": {
    "userId": 2,
    "name": "Jane Smith Updated",
    "email": "jane@example.com",
    "contactNo": "+1234567892",
    "address": "456 Oak Ave",
    "role": "salesperson",
    "profileBgColor": "#33FF57",
    "isActive": false,
    "createdAt": "2024-01-15T12:00:00.000Z",
    "lastLoginAt": null
  },
  "message": "User updated"
}
```

### 5. Delete User
**DELETE** `/users/{id}`

**Success Response (200):**
```json
{
  "status": "success",
  "data": null,
  "message": "User deleted"
}
```

---

## Clients Management

### 1. Get All Clients
**GET** `/clients?limit=10&offset=0`

**Success Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "clientId": 1,
      "name": "ABC Corporation",
      "contactNo": "+1234567890",
      "email": "contact@abc.com",
      "address": "123 Business St",
      "createdAt": "2024-01-01T00:00:00.000Z"
    }
  ],
  "pagination": {
    "total": 15,
    "limit": 10,
    "offset": 0,
    "hasNext": true,
    "hasPrev": false
  },
  "message": "Clients retrieved"
}
```

### 2. Get Client by ID
**GET** `/clients/{id}`

### 3. Create Client
**POST** `/clients`

**Request Body:**
```json
{
  "name": "XYZ Company",
  "contactNo": "+1234567891",
  "email": "info@xyz.com",
  "address": "456 Corporate Blvd"
}
```

### 4. Update Client
**PATCH** `/clients/{id}`

### 5. Delete Client
**DELETE** `/clients/{id}`

---

## Tasks Management

### 1. Get All Tasks
**GET** `/tasks?limit=10&offset=0`

**Success Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "taskId": 1,
      "dealNo": "DEAL-2024-001",
      "name": "Office Interior Design",
      "createdAt": "2024-01-01T00:00:00.000Z",
      "startDate": "2024-01-15",
      "dueDate": "2024-02-15",
      "priority": "High",
      "remarks": "Client wants modern design",
      "status": "In Progress",
      "createdBy": 1,
      "clientId": 1,
      "designerId": 1,
      "agencyId": null
    }
  ],
  "pagination": {
    "total": 50,
    "limit": 10,
    "offset": 0,
    "hasNext": true,
    "hasPrev": false
  },
  "message": "Tasks retrieved"
}
```

### 2. Get Task by ID
**GET** `/tasks/{id}`

### 3. Create Task
**POST** `/tasks`

**Request Body:**
```json
{
  "name": "Restaurant Interior Design",
  "startDate": "2024-02-01",
  "dueDate": "2024-03-01",
  "priority": "Medium",
  "remarks": "Focus on lighting and ambiance",
  "status": "Created",
  "clientId": 2,
  "designerId": 1
}
```

### 4. Update Task
**PATCH** `/tasks/{id}`

### 5. Update Task Status
**PATCH** `/tasks/{id}/status`

**Request Body:**
```json
{
  "status": "Completed"
}
```

**Available Status Values:**
- `Created`
- `Measurement: Done`
- `Quote: Done`
- `Approved`
- `In Progress`
- `Completed`
- `Cancelled`

### 6. Delete Task
**DELETE** `/tasks/{id}`

---

## Designers Management

### 1. Get All Designers
**GET** `/designers?limit=10&offset=0`

**Success Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "designerId": 1,
      "name": "Sarah Johnson",
      "firmName": "Johnson Design Studio",
      "contactNo": "+1234567890",
      "address": "789 Design Ave",
      "profileBgColor": "#FF33A1",
      "createdAt": "2024-01-01T00:00:00.000Z"
    }
  ],
  "pagination": {
    "total": 8,
    "limit": 10,
    "offset": 0,
    "hasNext": false,
    "hasPrev": false
  },
  "message": "Designers retrieved"
}
```

### 2. Get Designer by ID
**GET** `/designers/{id}`

### 3. Create Designer
**POST** `/designers`

**Request Body:**
```json
{
  "name": "Mike Wilson",
  "firmName": "Wilson Interiors",
  "contactNo": "+1234567891",
  "address": "321 Creative St",
  "profileBgColor": "#3357FF"
}
```

### 4. Update Designer
**PATCH** `/designers/{id}`

### 5. Delete Designer
**DELETE** `/designers/{id}`

---

## Service Master Management

### 1. Get All Services
**GET** `/service-master?limit=10&offset=0`

**Success Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "serviceMasterId": 1,
      "name": "Wall Painting",
      "description": "Interior wall painting service",
      "defaultUnitPrice": "25.00",
      "unit": "sq ft",
      "createdAt": "2024-01-01T00:00:00.000Z"
    }
  ],
  "pagination": {
    "total": 20,
    "limit": 10,
    "offset": 0,
    "hasNext": true,
    "hasPrev": false
  },
  "message": "Services retrieved"
}
```

### 2. Get Service by ID
**GET** `/service-master/{id}`

### 3. Create Service
**POST** `/service-master`

**Request Body:**
```json
{
  "name": "Flooring Installation",
  "description": "Hardwood flooring installation",
  "defaultUnitPrice": "45.00",
  "unit": "sq ft"
}
```

### 4. Update Service
**PATCH** `/service-master/{id}`

### 5. Delete Service
**DELETE** `/service-master/{id}`

---

## Measurements Management

### 1. Get All Measurements
**GET** `/measurements?limit=10&offset=0`

### 2. Get Measurements by Task
**GET** `/measurements/task/{taskId}?limit=10&offset=0`

**Success Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "measurementId": 1,
      "taskId": 1,
      "location": "Living Room",
      "width": "12.5",
      "height": "10.0",
      "area": "125.0",
      "unit": "sq ft",
      "quantity": 1,
      "unitPrice": "25.00",
      "discount": "5.00",
      "totalPrice": "118.75",
      "notes": "Include primer"
    }
  ],
  "pagination": {
    "total": 5,
    "limit": 10,
    "offset": 0,
    "hasNext": false,
    "hasPrev": false
  },
  "message": "Measurements retrieved"
}
```

### 3. Get Measurement by ID
**GET** `/measurements/{id}`

### 4. Create Measurement
**POST** `/measurements`

**Request Body:**
```json
{
  "taskId": 1,
  "location": "Bedroom",
  "width": "10.0",
  "height": "8.0",
  "area": "80.0",
  "unit": "sq ft",
  "quantity": 1,
  "unitPrice": "25.00",
  "discount": "0.00",
  "totalPrice": "2000.00",
  "notes": "Two coats required"
}
```

### 5. Update Measurement
**PATCH** `/measurements/{id}`

### 6. Delete Measurement
**DELETE** `/measurements/{id}`

---

## Task Services Management

### 1. Get All Task Services
**GET** `/task-services?limit=10&offset=0`

### 2. Get Task Services by Task
**GET** `/task-services/task/{taskId}?limit=10&offset=0`

### 3. Create Task Service
**POST** `/task-services`

### 4. Update Task Service
**PATCH** `/task-services/{id}`

### 5. Delete Task Service
**DELETE** `/task-services/{id}`

---

## Task Messages Management

### 1. Get All Messages
**GET** `/task-messages?limit=10&offset=0`

### 2. Get Messages by Task
**GET** `/task-messages/task/{taskId}?limit=10&offset=0`

### 3. Create Message
**POST** `/task-messages`

### 4. Update Message
**PATCH** `/task-messages/{id}`

### 5. Delete Message
**DELETE** `/task-messages/{id}`

---

## Timelines Management

### 1. Get All Timelines
**GET** `/timelines?limit=10&offset=0`

### 2. Get Timelines by Task
**GET** `/timelines/task/{taskId}?limit=10&offset=0`

### 3. Create Timeline
**POST** `/timelines`

### 4. Delete Timeline
**DELETE** `/timelines/{id}`

---

## Quotes Management

### 1. Get All Quotes
**GET** `/quotes?limit=10&offset=0`

### 2. Get Quote by Task
**GET** `/quotes/task/{taskId}`

**Success Response (200):**
```json
{
  "status": "success",
  "data": {
    "quoteId": 1,
    "taskId": 1,
    "createdAt": "2024-01-15T00:00:00.000Z",
    "subtotal": "2500.00",
    "tax": "250.00",
    "total": "2750.00",
    "notes": "10% tax included"
  },
  "message": "Quote retrieved"
}
```

### 3. Get Quote by ID
**GET** `/quotes/{id}`

### 4. Create Quote
**POST** `/quotes`

**Request Body:**
```json
{
  "taskId": 1,
  "subtotal": "2500.00",
  "tax": "250.00",
  "total": "2750.00",
  "notes": "10% tax included"
}
```

### 5. Update Quote
**PATCH** `/quotes/{id}`

### 6. Delete Quote
**DELETE** `/quotes/{id}`

---

## Bills Management

### 1. Get All Bills
**GET** `/bills?limit=10&offset=0`

### 2. Get Bill by Task
**GET** `/bills/task/{taskId}`

### 3. Get Bill by ID
**GET** `/bills/{id}`

### 4. Create Bill
**POST** `/bills`

### 5. Update Bill
**PATCH** `/bills/{id}`

### 6. Delete Bill
**DELETE** `/bills/{id}`

---

## Config Management (Admin Only)

### 1. Get All Config
**GET** `/config?limit=10&offset=0`

### 2. Get Config by Key
**GET** `/config/{key}`

### 3. Create Config
**POST** `/config`

### 4. Update Config
**PATCH** `/config/{key}`

### 5. Delete Config
**DELETE** `/config/{key}`

---

## Common Error Responses

### 400 - Bad Request
```json
{
  "message": "Invalid request data"
}
```

### 401 - Unauthorized
```json
{
  "message": "Access token required"
}
```

### 403 - Forbidden
```json
{
  "message": "Insufficient permissions"
}
```

### 404 - Not Found
```json
{
  "message": "Resource not found"
}
```

### 409 - Conflict
```json
{
  "message": "Email already exists"
}
```

### 422 - Validation Error
```json
{
  "message": "Validation failed: email must be a valid email address"
}
```

### 500 - Internal Server Error
```json
{
  "message": "An unexpected error occurred"
}
```

---

## Pagination

All list endpoints support pagination with query parameters:
- `limit`: Number of items per page (default: 10, max: 100)
- `offset`: Number of items to skip (default: 0)

**Example:** `/users?limit=20&offset=40`

**Pagination Response Format:**
```json
{
  "pagination": {
    "total": 150,
    "limit": 20,
    "offset": 40,
    "hasNext": true,
    "hasPrev": true
  }
}
```

---

## User Roles

- **admin**: Full access to all resources
- **salesperson**: Access to clients, tasks, and related resources
- **agent**: Limited access based on assigned tasks

---

## Priority Levels

For tasks:
- `Low`
- `Medium`
- `High`
- `Urgent`

---

## Status Flow

Task status progression:
1. `Created` → Initial state
2. `Measurement: Done` → Measurements completed
3. `Quote: Done` → Quote generated
4. `Approved` → Client approved the quote
5. `In Progress` → Work started
6. `Completed` → Work finished
7. `Cancelled` → Task cancelled (can be set from any state)

---

## Rate Limiting

- 100 requests per minute per IP address
- 1000 requests per hour per authenticated user

---

## Swagger Documentation

Interactive API documentation available at:
```
http://localhost:3000/swagger
```

This provides a complete interface to test all endpoints with proper request/response examples.