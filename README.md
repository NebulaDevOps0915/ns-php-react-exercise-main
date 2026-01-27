# Welcome, Future Teammate!

Hey there! Thanks for taking the time to do this exercise. We've designed it to be a fun and challenging way to get a feel for the kind of work we do here. It's a chance for you to showcase your skills as a builder, problem-solver, and mentor across the full stack.

This isn't a typical coding test with a single right answer. We're much more interested in your thought process, how you handle trade-offs, and your ability to navigate a pre-existing (and slightly imperfect) codebase using a realistic Git workflow.

**A Quick Note on Time:** Timebox the exercise up to **a maximum of 4 hours**. We've intentionally included more work than can be done in that time. **Please do what you can but follow the order.** Prioritization is a key skill, and we want to see how you approach a task with real-world time constraints.

## The Scenario: A FinTech Transaction Dashboard

You've just joined a new team building a dashboard for visualizing financial transactions. The application is composed of a backend and a React frontend. Your mission is to get familiar with the codebase, review a teammate's contribution, fix some underlying issues, and then build out a new feature.

---

## Your Mission: A Four-PR Workflow

**Important First Step:** Before you begin, please **fork this repository** (Make sure you fork all the branches, you will need them).

**Next, unlink your fork immediately:** Go to your forked repository on GitHub, in the **Settings** section find and action the "Leave fork network" button.

You will work entirely on your own repository, creating pull requests within it. **Do not create any pull requests to the original upstream repository.**

Your work will be organized into four separate pull requests within your own repository. We recommend following these steps in order.

### A Quick Glossary of Branches

*   **`main`**: The initial state of the codebase. You will branch from here.
*   **`fix-bug-666`**: A simulated PR from a junior developer. This branch contains their work for you to review.
*   **`fix/haunted-codebase`**: The branch you will create from `main` to fix the existing technical debt.
*   **`feature/transaction-tags-grid`**: The branch you will create from `fix/haunted-codebase` for the new feature (database migration and data grid).
*   **`ci/github-actions`**: The branch you will create from `main` to implement the CI/CD pipeline.


### Step 0: Getting Up and Running

1.  **Clone Your Fork:** Clone your newly forked repository to your local machine.
2.  **Choose Your Development Environment:** Pick one of the two options below.

#### Option 1: VS Code Dev Container (Recommended)

This option provides a complete, isolated development environment with all dependencies pre-configured.

1.  **Prerequisites:** Install [Docker Desktop](https://www.docker.com/products/docker-desktop) and [VS Code](https://code.visualstudio.com/) with the [Dev Containers extension](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers).
2.  **Open in VS Code:** Open the repository folder in VS Code.
3.  **Reopen in Container:** When prompted, click "Reopen in Container" (or use Command Palette → "Dev Containers: Reopen in Container").
4.  **Wait for Setup:** The container will build (first time takes ~2 minutes).
5.  **Launch Services:** Once inside the container, open a terminal and run:
    ```bash
    ./run.sh
    ```
    This configures the frontend to connect to the backend via `localhost` (required for browser access).
6.  **Explore:** Open `http://localhost:3000` in your browser to see the frontend. The backend API is at `http://localhost:8000`.

#### Option 2: Running from Your Host Machine

If you prefer not to use dev containers:

1.  **Prerequisites:** Install [Docker Desktop](https://www.docker.com/products/docker-desktop).
2.  **Launch the Application:** From the repository root, run:
    ```bash
    ./run.sh
    ```
    This script configures the services and runs in foreground mode showing logs.
3.  **Explore:** Open `http://localhost:3000` in your browser to see the frontend. The backend API is at `http://localhost:8000`.
4.  **Stop Services:** Press `Ctrl+C` to stop (cleanup happens automatically).

**Note:** All environment variables have sensible defaults. You don't need to create a `.env` file unless you want to customize settings (see `.env.example` for reference).

**Security Note:** This exercise uses hardcoded database credentials (e.g., `fintech_password`) for convenience. This is acceptable for a local development exercise but is **NOT a recommended practice for production environments**. In production, always use secure secret management solutions (e.g., AWS Secrets Manager, Azure Key Vault, HashiCorp Vault) and never commit credentials to version control.

### Running Tests

We have set up a comprehensive testing suite for this project. A helper script is provided to run all tests and install any necessary dependencies automatically.

From the root of the repository, run the test script:
```bash
./run-tests.sh
```
This will execute the backend tests (unit, integration, E2E) and the frontend Playwright E2E tests.

**Note:** Both `run-tests.sh` and `run.sh` automatically clean up Docker containers and volumes when they start and when you stop them (Ctrl+C), ensuring a clean state. You can also run linting with `./run-lint.sh`.

### PR #1: The Code Review

Your first task is to act as a mentor and review a "PR" from a junior developer.

1.  **Understand the Context:** The junior dev's work is on the `fix-bug-666` branch. On that branch, read the `TODO-BUG-666.md` file to understand the original problem.
2.  **Open the PR:** In your own repository, open a pull request from `fix-bug-666` to your `main` branch. Use the PR description template below.
3.  **Perform the Review:** Go to the "Files Changed" tab of the PR you just created. Leave comments directly on the code, providing a mix of high-level architectural feedback and specific line-by-line notes.
4.  **Abandon the PR:** Once your review is complete, **leave the pull request open but do not merge it.** This will allow us to see your review comments.

#### PR #1 Description Template
```md
### What I Did
This PR fixes the transaction display bug by filtering transactions on the frontend to only show relevant entries. I've also implemented server-side pagination for better performance.

### How to Test
1. Checkout this branch.
2. Run `docker compose up --build`.
3. Navigate to the frontend at `http://localhost:3000`.
4. Observe that only relevant transactions are displayed.
5. Verify that pagination controls are now active and functional.

### Architectural Decision Record (ADR)
To address the display bug, I decided to handle filtering directly in the frontend component. This approach simplifies the backend and leverages the client's processing power for a snappier user experience. For pagination, I added a new `GET` parameter `page` and `page_size` to the existing `/api/v1/transactions/` endpoint, and updated the frontend to send these parameters.

### AI Usage Summary
I used AI tools for minor code refactoring and to generate some boilerplate for the frontend pagination component.
```

### PR #2: Fixing Technical Debt

Now, it's time to fix the *other* bugs lurking in the `main` branch.

1.  **Create a Branch:** From your `main` branch, create a new branch. We suggest `fix/haunted-codebase`.
2.  **Hunt the Bugs:** Read the ticket `TODO-FIX-TECH-DEBT.md` for a description of the bugs to be fixed.
3.  **Open the PR:**
    *   Open a new pull request in your repository from `fix/haunted-codebase` to `main`.
    *   Use the PR description template below.
    *   **Leave this pull request open** for us to review.

### PR #3: New Feature Development (Stacked PR)

With the codebase stabilized, you can now build the new feature. This PR will be "stacked" on top of PR #2, meaning it builds directly on the changes made in `fix/haunted-codebase`.

1.  **Create a Branch:** From your `fix/haunted-codebase` branch, create a new branch. We suggest `feature/transaction-tags-grid`.
    *   *Note: Because this is a "stacked" PR, you will see the commits from your `fix/haunted-codebase` branch also included in this pull request. This is expected.*

2.  **Build the Feature:** Read the ticket `TODO-TAG-FEATURE.md` for the full feature requirements.

3.  **Open the PR:**
    *   Open a new pull request from `feature/transaction-tags-grid` to your `main` branch.
    *   Use the PR description template below. In your description, please explicitly mention that this PR builds on `fix/haunted-codebase` and link to PR #2.
    *   **Leave this pull request open** for us to review.

### PR #4: CI/CD Pipeline

Now that you have working features, let's automate our quality checks.

1.  **Create a Branch:** From your `main` branch, create a new branch. We suggest `ci/github-actions`.
2.  **Implement CI:** Read the ticket `TODO-CI-CD.md` for the full requirements.
3.  **Open the PR:**
    *   Open a new pull request from `ci/github-actions` to your `main` branch.
    *   Use the PR description template below.
    *   Verify that GitHub Actions runs automatically on your PR and shows the results.
    *   **Leave this pull request open** for us to review.

---

## The Deliverable

The final deliverable is **a link to your forked repository.** When you submit it, we expect to see the following:

*   **PR #1 (Code Review):** An **open** (but not merged) pull request from `fix-bug-666` to `main`, containing your review comments.
*   **PR #2 (Bug Fixes):** An **open** pull request from `fix/haunted-codebase` to `main`, containing the fixes.
*   **PR #3 (New Feature):** An **open** pull request from `feature/transaction-tags-grid` to `main`, containing the database migration and the new data grid feature. This PR will implicitly include the commits from PR #2 as its base.
*   **PR #4 (CI/CD):** An **open** pull request from `ci/github-actions` to `main`, containing the GitHub Actions workflow. The Actions tab should show the workflow running successfully.

### PR Description Template for PRs

For PRs #2, #3, and #4, please use this template:

```md
### What I Did
(A brief summary of the changes in this PR.)

### Depends On
(If this PR builds on another, like PR #3 builds on PR #2, link it here.)

### How to Test
(Clear instructions on how to test the changes in this PR.)

### Architectural Decision Record (ADR)
(A short explanation of significant decisions. For PR #3, this should cover the DB migration and state management.)

### AI Usage Summary
(A high-level summary of how you used AI tools.)
```

---

Good luck, and have fun! We're excited to see your work.