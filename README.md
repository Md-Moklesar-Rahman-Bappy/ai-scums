# 🎓 AI SCUMS
### AI-Powered School, College & University Management System

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-red?styleadge&logo=laravel
  <img src="https://img.shields.io/badge/PHP-tyle=for-the-badge&logo=php
  <img src="https://img.shields.io/badge/Bootstrap-5-purple?style=for-the-=bootstrap
  <img src="https://img.shields.io/badge/MySQL-Database-orange-the-badge&logo=mysql
  <img src="https://img.shields.io/badge/OpenAI-AI_Assistant-green?style=for&logo=openai
</p>

<p align="center">
  <b>An Intelligent Education Management Platform for Schools, Colleges and Universities.</b>
</p>

---

## 📖 Overview

AI SCUMS (AI-powered School, College & University Management System) is a modern educational ERP platform developed using Laravel.

The system provides centralized management for:

- Schools
- Colleges
- Universities
- Training Institutes

Along with traditional ERP features, AI SCUMS introduces an intelligent AI Assistant capable of answering academic, administrative, and institutional queries using role-based access control.

---

# 🚀 Key Features

## 👨‍🎓 Student Module

- Student Admission
- Student Profiles
- Attendance Management
- Academic Records
- CGPA Calculation
- Exam Results
- Class Schedule
- Fee Management
- AI Academic Assistant

---

## 👨‍🏫 Teacher Module

- Teacher Profiles
- Course Management
- Attendance Monitoring
- Evaluation Management
- Student Performance Reports
- AI Teaching Assistant

---

## 🏢 Administration Module

- Admission Analytics
- Enrollment Reports
- Financial Reports
- Outstanding Fee Tracking
- Institution Dashboard
- Academic Monitoring

---

## 👨‍👩‍👧 Parent Module

- Child Attendance Tracking
- Academic Progress Monitoring
- Exam Notifications
- AI Parent Assistant

---

# 🤖 AI Assistant

One of the core innovations of AI SCUMS is the built-in AI Assistant.

The assistant can answer questions like:

### Student

```text
What is my attendance percentage?
When is my next exam?
What is my current CGPA?
Show my class schedule.
```

### Teacher

```text
Show low attendance students.
Which courses are performing poorly?
What evaluations are pending?
```

### Administrator

```text
Show admission statistics.
Generate enrollment reports.
Show outstanding fees.
```

---

# 🔐 Security Features

AI SCUMS follows enterprise-level security practices.

### Role-Based Access Control (RBAC)

Supported Roles:

- Super Admin
- Institution Admin
- Accountant
- Teacher
- Student
- Parent

### Security Layers

- Authentication
- Authorization
- Tenant Isolation
- Session Protection
- CSRF Protection
- Data Access Validation
- AI Query Authorization Gate

---

# 🏗️ System Architecture

```text
User Query
     │
     ▼
Intent Detection
     │
     ▼
Authorization Gate
     │
     ▼
Query Router
     │
     ▼
Database Retrieval
     │
     ▼
Response Formatter
     │
     ▼
AI Assistant Response
```

---

# 🛠️ Technology Stack

## Backend

- Laravel 12
- PHP 8.3+
- REST APIs
- Eloquent ORM

## Frontend

- HTML5
- CSS3
- Bootstrap 5
- JavaScript
- AJAX

## Database

- MySQL

## AI Layer

- OpenAI Integration
- Intent Detection
- AI Assistant
- Natural Language Processing

---

# 📦 Installation

### Clone Repository

```bash
git clone https://github.com/Md-Moklesar-Rahman-Bappy/ai-scums.git
```

### Open Project

```bash
cd ai-scums
```

### Install Dependencies

```bash
composer install
```

### Setup Environment

```bash
cp .env.example .env
```

### Generate Application Key

```bash
php artisan key:generate
```

### Configure Database

Update your `.env`

```env
DB_DATABASE=ai_scums
DB_USERNAME=root
DB_PASSWORD=
```

### Run Migration

```bash
php artisan migrate
```

### Start Server

```bash
php artisan serve
```

---

# 📂 Project Structure

```text
app/
 ├── Models
 ├── Http
 ├── Services
 │    └── AI
 │         ├── Intent.php
 │         ├── IntentClassifier.php
 │         ├── AuthorizationGate.php
 │         ├── QueryRouter.php
 │         ├── AssistantService.php
 │         └── ResponseFormatter.php
 └── Providers

resources/
database/
routes/
storage/
```

---

# 🎯 Project Goals

- Centralized Education Management
- AI-Powered User Experience
- Data-Driven Decision Making
- Multi-Institution Support
- Secure Role-Based Access
- Academic Process Automation

---

# 🌟 Future Roadmap

- Voice Assistant
- Multilingual Support
- Mobile Apps
- AI Report Generation
- Predictive Student Analytics
- Smart Attendance Intelligence
- AI Chatbot Enhancement

---

# 👨‍💻 Developer

**Md. Moklesar Rahman Bappy**

Laravel Developer | AI Enthusiast | Full Stack Web Developer

### GitHub

[AI SCUMS Repository](https://github.com/Md-Moklesar-Rahman-Bappy/ai-scums)

---

# 📄 License

This project is licensed under the MIT License.

---

## ⭐ Support

If you like this project, please give it a ⭐ on GitHub and support future development.

**AI SCUMS — Shaping the Future of Educational Management with Artificial Intelligence.**
``