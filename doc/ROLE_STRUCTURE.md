# Role Structure and Permissions

## Overview
The system has been unified with clear role-based access control. All users access the system through proper authentication and routing.

## Roles

### 1. Admin (Superadmin)
**Full CRUD Operations** - Complete control over the system
- **Routes:** `admin.superadmin.*`
- **Access Level:** Everything
- **Permissions:**
  - ✅ Create requests
  - ✅ Read/View all requests
  - ✅ Update requests (inline edit + status + notes)
  - ✅ Delete requests (soft delete)
  - ✅ Bulk operations (status update, add notes, delete, export)
  - ✅ User management
  - ✅ Document type management
  - ✅ System settings
   - ✅ View activity timeline
  - ✅ Trash management (restore/force delete)
  - ✅ Export data

### 2. Registrar
**Create, Read, Manage** - Day-to-day request handling
- **Routes:** `admin.requests.*`
- **Access Level:** Standard admin operations
- **Permissions:**
  - ✅ Create requests
  - ✅ Read/View all requests
  - ✅ Update request status
  - ✅ Update request notes
  - ✅ Bulk operations (status update, add notes, export)
  - ❌ Delete requests (no delete permission)
  - ❌ User management
  - ❌ System configuration
  - ❌ Document type management

### 3. Requester (Student/Public)
**Track Requests Only** - View their own submissions
- **Routes:** `dashboard`, `my-requests.*`
- **Access Level:** Personal data only
- **Permissions:**
  - ✅ Submit new requests (passwordless with OTP)
  - ✅ Track their own requests
  - ✅ View status updates
  - ❌ No admin panel access
  - ❌ Cannot see other users' requests

## Routes Structure

### Admin Routes (Registrar + Superadmin)
```
/admin/requests                  - List all requests
/admin/requests/create           - Create new request
/admin/requests/{id}             - View request details
/admin/requests/{id}/update      - Update request
/admin/requests/{id}/status      - Update status
/admin/requests/{id}/notes       - Update notes
/admin/requests/bulk-update      - Bulk status/notes update
/admin/requests/bulk-delete      - Bulk delete (superadmin only)
/admin/requests/export           - Export to CSV
```

### Superadmin-Only Routes
```
/admin/superadmin/dashboard                     - Superadmin dashboard
/admin/superadmin/requests                      - Full CRUD requests management
/admin/superadmin/requests/{id}                 - View/edit request
/admin/superadmin/requests/{id}/update          - Inline edit
/admin/superadmin/requests/{id}/status          - Update status
/admin/superadmin/requests/{id}/notes           - Update notes
/admin/superadmin/requests/{id}/destroy         - Delete request
/admin/superadmin/requests/bulk-action          - Bulk operations
/admin/superadmin/requests/export               - Export to CSV
/admin/superadmin/users                         - User management
/admin/superadmin/document-types                - Document type CRUD
/admin/superadmin/settings                      - System settings
/admin/superadmin/logs                          - Activity timeline
/admin/superadmin/trash/requests                - Trash management
```

### Public Routes
```
/                                    - Landing page
/request                             - Select document type
/request/verify                      - Email verification with OTP
/request/form                        - Request form
/track                               - Track request by tracking ID
/dashboard                           - User dashboard (authenticated)
/my-requests                         - User's own requests
```

## Key Changes Made

1. **Added Missing Routes:**
   - `admin.superadmin.requests.show` - View single request
   - `admin.superadmin.requests.update` - Update request
   - `admin.superadmin.requests.update-status` - Update status
   - `admin.superadmin.requests.update-notes` - Update notes
   - `admin.superadmin.requests.export` - Export requests
   - `admin.requests.bulk-delete` - Bulk delete (superadmin check in controller)
   - `admin.requests.export` - Export requests

2. **Added Missing Controller Methods:**
   - `SuperadminRequestController::show()` - Display single request
   - `SuperadminRequestController::update()` - Inline edit
   - `SuperadminRequestController::updateStatus()` - Update status
   - `SuperadminRequestController::updateNotes()` - Update notes
   - `SuperadminRequestController::export()` - Export to CSV
   - `RequestManagementController::bulkDelete()` - Bulk delete with superadmin check
   - `RequestManagementController::export()` - Export to CSV

3. **Created Superadmin Show Page:**
   - `resources/js/Pages/Admin/Superadmin/Requests/Show.vue`
   - Copied from Admin version with updated routes

4. **Fixed Frontend Routing:**
   - Superadmin pages now use `admin.superadmin.requests.*` routes
   - Admin/Registrar pages use `admin.requests.*` routes
   - All View/Edit links properly route to respective controllers

5. **Unified Page Titles:**
   - Both Admin and Superadmin use "Manage Requests" title
   - Clear distinction only in functionality, not in naming

## Middleware Protection

- **role:admin** - Allows both superadmin and registrar
- **role:superadmin** - Superadmin only
- Middleware checks in `app/Http/Middleware/RoleMiddleware.php`
- Additional checks in controllers for delete operations

## Database Roles

Available roles in `users` table:
- `superadmin` - Full system access
- `registrar` - Standard admin access
- `user` - Public user (requester)

## Security Notes

1. Delete operations require explicit superadmin check in controllers
2. Bulk operations validate user role before execution
3. Soft deletes used for requests (can be restored by superadmin)
4. All mutations logged in `request_logs` table
5. Email notifications sent on status changes
