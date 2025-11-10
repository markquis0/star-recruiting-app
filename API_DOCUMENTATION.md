# API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication

All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {token}
```

## Endpoints

### Authentication

#### Register
```
POST /api/register
```

**Request Body:**
```json
{
  "username": "john_doe",
  "password": "password123",
  "role": "candidate",
  "first_name": "John",
  "last_name": "Doe",
  "role_title": "Software Engineer",
  "years_exp": 5
}
```

For recruiters:
```json
{
  "username": "recruiter1",
  "password": "password123",
  "role": "recruiter",
  "first_name": "Jane",
  "last_name": "Smith",
  "email": "jane@company.com",
  "company_name": "Tech Recruiters Inc"
}
```

**Response:**
```json
{
  "message": "Registration successful",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "username": "john_doe",
    "role": "candidate"
  }
}
```

#### Login
```
POST /api/login
```

**Request Body:**
```json
{
  "username": "john_doe",
  "password": "password123"
}
```

**Response:**
```json
{
  "message": "Login successful",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "username": "john_doe",
    "role": "candidate"
  }
}
```

### Candidate Endpoints

#### Get All Forms
```
GET /api/candidate/home
```

**Response:**
```json
{
  "forms": [
    {
      "id": 1,
      "candidate_id": 1,
      "form_type": "behavioral",
      "status": "submitted",
      "data": {},
      "review_count": 2,
      "assessment": {
        "id": 1,
        "category": "Analytical",
        "score_summary": {
          "analytical": 85,
          "collaborative": 60,
          "persistent": 40,
          "social": 50
        }
      }
    }
  ]
}
```

#### Create/Update Form
```
POST /api/candidate/form
```

**Request Body:**
```json
{
  "form_type": "project",
  "data": {
    "project_name": "E-commerce Platform",
    "description": "Built a full-stack e-commerce platform"
  }
}
```

#### Get Form by ID
```
GET /api/candidate/form/{id}
```

#### Submit Assessment
```
POST /api/candidate/assessment/{form_id}
```

**Request Body:**
```json
{
  "answers": [
    {
      "question_id": 1,
      "answer": "5"
    },
    {
      "question_id": 2,
      "answer": "4"
    }
  ]
}
```

#### Get Results
```
GET /api/candidate/results
```

### Recruiter Endpoints

#### Get Saved Candidates
```
GET /api/recruiter/home
```

#### Search Candidates
```
GET /api/recruiter/search?role=Engineer&years_exp=3&behavioral_category=Analytical
```

**Query Parameters:**
- `role` - Filter by role title
- `years_exp` - Minimum years of experience
- `behavioral_category` - Filter by behavioral category
- `aptitude_category` - Filter by aptitude category (High Aptitude, Moderate Aptitude, Low Aptitude)

#### Save Candidate
```
POST /api/recruiter/save
```

**Request Body:**
```json
{
  "candidate_id": 1
}
```

#### Remove Saved Candidate
```
DELETE /api/recruiter/save/{id}
```

#### View Candidate Profile
```
GET /api/recruiter/candidate/{id}
```

**Note:** This endpoint increments the `review_count` for all forms belonging to the candidate.

### Assessment Endpoints

#### Evaluate Behavioral Assessment
```
POST /api/assessments/behavioral/evaluate
```

**Request Body:**
```json
{
  "assessment_id": 1
}
```

#### Evaluate Aptitude Assessment
```
POST /api/assessments/aptitude/evaluate
```

**Request Body:**
```json
{
  "assessment_id": 2
}
```

## Error Responses

All errors follow this format:
```json
{
  "message": "Error message",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

## Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

