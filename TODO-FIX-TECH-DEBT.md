# Ticket: Fix Haunted Codebase

## Objective
Address two separate, pre-existing technical debt issues in the `main` branch to improve performance and user experience.

---

### 1. Backend Report: Inefficient Database Query

**Symptom:** Users are reporting that the main transaction list is loading very slowly, especially for users with many transactions.

**Task:**
Investigate the API endpoint responsible for fetching the list of transactions. Our initial analysis suggests the endpoint is making an excessive number of database calls—likely one for each transaction—to retrieve related information (like the category name).

Your goal is to identify and fix this inefficiency. The solution should retrieve all required information in a constant number of database queries, regardless of how many transactions are being fetched.

---

### 2. Frontend Report: Unnecessary Component Re-renders

**Symptom:** The product owner noticed that when they interact with certain UI elements in the header (e.g., hovering over the user profile icon), the main transaction data grid seems to flicker and re-render, even though the transaction data itself has not changed. This is causing a sluggish feel on the dashboard.

**Task:**
Using browser developer tools (like the React Profiler), confirm the unnecessary re-rendering of the transaction list component.

Your goal is to identify the root cause of the wasted renders and apply a fix to ensure the transaction list only re-renders when its data or relevant state actually changes.
