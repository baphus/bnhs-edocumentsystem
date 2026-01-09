# BNHS E-Document System

## Project Concept
The **BNHS E-Document System** is a streamlined, web-based platform designed to digitize and simplify the process of requesting academic documents for students and alumni of **Barangay National High School**.

**Who it is for**:
*   **Students & Alumni**: To easily request transcripts, certifications, and other records without visiting the campus physically.
*   **Registrar Staff**: To efficiently manage, track, and process incoming requests.
*   **Administrators**: To oversee system users, configurations, and audit stamps.

**Core Problem Solved**:
Eliminates the need for manual, paper-based request forms and long queues, providing a secure, transparent, and trackable document processing workflow.

---

## User Experience (UX) Features
*   **Passwordless Request Flow**: Users can initiate requests using just their email and a secure OTP validation—no permanent account required.
*   **Real-Time Tracking**: Applicants can check the status of their documents instantly using a unique **Tracking ID**.
*   **Dynamic Request Wizard**: A step-by-step form that guides users through selecting document types and providing necessary academic details (LRN, Strand, etc.).
*   **Automated Notifications**: System-triggered email updates when a request status changes (e.g., from 'Pending' to 'Ready for Pickup').
*   **Role-Based Dashboards**: tailored interfaces for Registrars to process requests and Admins to manage the system.
*   **Responsive Design**: Fully optimized for mobile and desktop devices.

---

## User Roles & Permissions
The system employs a strict Role-Based Access Control (RBAC) model.

| Role | Access Level | Responsibilities |
| :--- | :--- | :--- |
| **Guest / Applicant** | Public | • Submit document requests via OTP verification.<br>• Track status of existing requests using a Tracking ID. |
| **Registrar** | Restricted | • View, filter, and export document requests.<br>• Update request statuses (e.g., *Processing*, *Completed*).<br>• Add administrative notes to requests. |
| **Admin** | Full Access | • Manage system users (Create, Edit, Deactivate).<br>• Configure Document Types and Requirements.<br>• View Audit Logs and System Activity.<br>• Manage Global Settings. |

---

## Inferred Data Models
The architecture relies on the following core logical objects:

*   **DocumentRequest**: The central entity tracking the request lifecycle. Contains individual details (LRN, Name), requested document type, current status, and generated Tracking ID.
*   **User**: Represents system actors with authentication credentials (Admins, Registrars) and role definitions.
*   **DocumentType**: A catalog of available documents (e.g., *Form 137*, *Certificate of Enrollment*) defining specific naming conventions and categories.
*   **RequestLog**: A history trail linking specific actions (Status Updates) to Users or System events for accountability.
*   **Otp**: Temporary security tokens for verifying guest identities during the request and tracking process.

---

## Core Site Map (Routes)
A high-level view of the application's navigation structure.

| Section | Route | Description |
| :--- | :--- | :--- |
| **Public Portal** | `/` | **Landing Page** - Introduction and services overview. |
| | `/request/select` | **Document Selection** - First step of request wizard. |
| | `/request/verify` | **Identity Verification** - Email & OTP entry. |
| | `/request/form` | **Request Form** - Academic details entry. |
| | `/track` | **Tracking Portal** - Check status via Tracking ID. |
| | `/my-requests/*` | **User Dashboard** - View history of personal requests. |
| **Registrar Panel** | `/registrar/dashboard` | **Overview** - Summary statistics of pending requests. |
| | `/registrar/requests` | **Request Management** - Table view to process requests. |
| **Admin Panel** | `/admin/dashboard` | **Main Dashboard** - System-wide analytics. |
| | `/admin/users` | **User Management** - CRUD operations for staff accounts. |
| | `/admin/settings` | **System Config** - Application-level adjustments. |

---

## Proposed Tech Stack
This project utilizes a modern, robust, and full-stack architecture.

*   **Frontend**: **Inertia.js** with **Vue 3** - For a modern, single-page application (SPA) feel within a server-driven framework.
*   **Backend**: **Laravel 12** - Providing a secure, scalable MVC structure with built-in authentication and eloquent ORM.
*   **Database**: **MySQL** - For reliable relational data storage (Users, Requests, Logs).
*   **Styling**: **Tailwind CSS** - For a utility-first, rapid UI development approach ensuring aesthetic consistency.
*   **Build Tool**: **Vite** - For ultra-fast asset compilation and hot module replacement (HMR).

---

## Future Enhancements
To further elevate the platform in Version 2.0:

1.  **SMS Integration**: Implement SMS notifications (via Twilio or similar) to alert applicants of status updates in real-time alongside emails.
2.  **Payment Gateway**: Integrate online status payments (GCash, PayMaya) directly within the request flow for paid documents.
3.  **Digital Document Issuance**: Generate secure, digitally signed PDF copies of requested documents for immediate download, reducing the need for physical pickup.
