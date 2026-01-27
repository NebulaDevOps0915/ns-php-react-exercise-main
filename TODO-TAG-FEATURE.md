# Ticket: Implement Transaction Tags & Grid

## Objective
Build a new feature that allows transactions to be categorized with multiple tags and display them in a new, advanced data grid with server-side sorting and pagination.

---

### 1. Database Schema Evolution

The core of this feature requires a schema change.

*   **Current State:** A `Transaction` has a many-to-one relationship with a single `Category`.
*   **Required Change:** Evolve the schema to support a **many-to-many relationship** between a `Transaction` and a new `Tag` entity. This will allow a single transaction to be associated with multiple tags (e.g., "work", "travel", "reimbursable").

**Task:**
Design and implement the necessary database schema changes (e.g., a new `Tag` model/table and an association table). Since this project does not use a dedicated migration tool, you should apply these changes by **modifying the database seeding script** located in the backend:
*   Python: `backend/seed.py`
*   Ruby: `backend/db/seeds.rb`
*   Golang: `backend/internal/database/seed.go`

After modifying the seed script, run the following command to apply the changes and seed the new data:
```bash
docker compose exec backend ./seed.sh
```

---

### 2. Backend Requirements: New Grid API

**Task:**
Create a **new, purpose-built API endpoint** to serve the data grid. This endpoint should be separate from existing transaction endpoints to avoid breaking changes.

*   **Endpoint:** `GET /api/v1/transactions/grid`
*   **Functionality:**
    *   **Server-Side Pagination:** Must accept `page` (1-indexed) and `size` query parameters. The default page size should be **10 transactions per page** to ensure pagination is necessary with the seed data.
    *   **Server-Side Sorting:** Must accept `sort_by` (column name) and `sort_order` (`asc`/`desc`) query parameters.
    *   **Data Fetching:** **Must use a raw SQL query** that performs a `JOIN` across multiple tables to retrieve transactions along with their associated category and *all* of their new tags.
*   **Response:** The JSON response should be an object containing the list of transaction items and the total number of transactions for pagination purposes (e.g., `{ "items": [...], "total": 25 }`).

---

### 3. Frontend Requirements: New Data Grid Component

**Task:**
Build a new React component (`TransactionGrid.tsx`) that consumes the new API endpoint.

*   **Display:** The grid must display columns for `Date`, `Description`, `Amount`, `Category`, and the new `Tags` (rendered as a collection of pills or badges).
*   **Server-Side Sorting:** Clicking a column header (e.g., `Amount`) must trigger a new API call to fetch data sorted by that column.
*   **Server-Side Pagination:** UI controls (e.g., "Next", "Previous" buttons) must trigger new API calls to fetch the correct page of data.
*   **Implementation:** You are encouraged to build the grid using standard HTML table elements rather than adding a new third-party library.
