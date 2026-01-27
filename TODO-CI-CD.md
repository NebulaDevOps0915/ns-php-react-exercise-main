# TODO: Set Up CI/CD Pipeline

## Background

Currently, we have a comprehensive test suite and linting tools set up for both the backend and frontend, but there's no automated way to run these checks when code is pushed or when pull requests are created. This leads to inconsistent code quality and potential bugs slipping through.

We need to implement a Continuous Integration (CI) pipeline using GitHub Actions that automatically runs our quality checks on every pull request and push to the main branch.

---

## Requirements

### GitHub Actions Workflow

Create a GitHub Actions workflow that:

1. **Triggers on:**
   - Pull requests to any branch
   - Pushes to the `main` branch

2. **Backend Quality Checks:**
   - Run linting/code quality checks for the backend code
   - Run all backend tests (unit, integration, and E2E)

3. **Frontend Quality Checks:**
   - Run linting/code quality checks for the frontend code
   - Run all frontend tests

### Implementation Notes

- The workflow file(s) should be placed in the `.github/workflows/` directory
- You have the freedom to structure the workflow as you see fit (single job, multiple jobs, job dependencies, etc.)
- Consider the appropriate Node.js and Python versions to use (check the existing Docker configuration for guidance)
- The CI should fail if any of the checks fail

### Testing Your Workflow

- Once you create the workflow file and push it to your fork, GitHub Actions will automatically run
- You can view the results in the "Actions" tab of your repository
- Ensure the workflow runs successfully and all checks pass

---

## Deliverable

- Open a pull request from `ci/github-actions` to `main` in your fork
- The PR should include the workflow file(s)
- Use the standard PR template (see README.md)
- The workflow should run automatically on the PR and show a green status

---

## Hints

- Review the `package.json` in the frontend to see what test and lint scripts are available
- Review the backend dependencies and common Python tooling practices
- Check the `docker-compose.yml` to understand the environment setup
- GitHub Actions has excellent documentation and many pre-built actions for common tasks
