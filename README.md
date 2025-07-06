# Freelancy - Freelance Platform  
A full-stack freelance management system built with **Laravel 10** (Backend) and **Vue 3** (Frontend) styled with **Tailwind CSS**.

## 🚀 Project Overview

Freelancy is a web application designed to connect **clients** and **freelancers** in a simple, intuitive platform where projects can be created, proposals submitted, and work managed seamlessly.

### 👥 User Types
- **Client:** Users who post projects and hire freelancers.
- **Freelancer:** Users who apply to projects and deliver work.

---

## 📝 How It Works

1. **Clients** create projects, specifying details like category, rate, and description.
2. **Freelancers** browse projects and submit proposals.
3. **Clients** can approve or reject proposals.
4. **Freelancers** can withdraw their proposal before it gets approved or rejected.
5. Once a proposal is **approved**, the **freelancer** can mark the project as **done**.

---

## 🔑 Key Features

### 🔗 Core Functionalities
- **Project Management:** Clients can create, view, and manage project listings.
- **Proposal System:** Freelancers can submit, withdraw, and track proposals.
- **Approval Workflow:** Clients can accept or reject proposals; freelancers can mark projects as completed.

### 💬 Real-Time Messaging
- Private messaging between users, powered by **Pusher** and **Laravel Echo** for real-time communication.
- Users can message others, including themselves.

### 👤 User Profiles
- Fully customizable profiles with editable:
  - Brief description
  - Skills
  - Work experience
  - Educational attainment

### 🔍 Search & Filtering
- Live search, filtering, and pagination with **debounced search** using **Lodash**.
- Route-based query parameters ensure shareable and reproducible search results via URL.

### 🔐 Authentication & Authorization
- Registration, login, logout, and **email verification** (SMTP).
- Stateless authentication using **JWT tokens**.
- Role-based **authorization**.

### ⚙️ Backend & Testing
- RESTful **JSON API** built with **Laravel 10**.
- Feature testing with **PHPUnit** for key functionalities.

### 🌐 Frontend Enhancements
- **SweetAlert2** for polished modal dialogs (confirmations, alerts, loading).
- **Moment.js** for consistent and human-readable date/time formatting.
- **Vue Router** for smooth client-side navigation.
- **Axios** for efficient communication with the backend.
- **Pinia** for reliable and reactive global state management.

---

## 🛠 Tech Stack

| Technology | Purpose |
|------------|---------|
| **Laravel 10** | Backend API & Business Logic |
| **Vue 3** | Frontend Interface |
| **Tailwind CSS** | Styling & Layout |
| **Pinia** | Frontend State Management |
| **JWT** | Authentication |
| **Pusher & Laravel Echo** | Real-Time Messaging |
| **PHPUnit** | Automated Testing |
| **Axios** | Frontend HTTP Requests |
| **SweetAlert2** | Modal Dialogs & Notifications |
| **Moment.js** | Date & Time Formatting |
| **Vue Router** | SPA Routing |
| **Lodash (Debounce)** | Optimized Search Input Handling |
