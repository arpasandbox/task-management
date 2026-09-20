# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Workspace switcher **more** menu per workspace: Edit, Delete, Add Member, and Remove Member (owner-only for delete and membership actions).
- Modals in the switcher to add members by email and remove non-owner members.

### Changed

- Workspace **create** control in the sidebar: rounded-square button styling.
- Workspace row layout: task count plus vertical more menu trigger after the count.

## [1.0.1] - 2026-09-06

### Changed

- Expanded `README.md` with project overview, feature list, tech stack, and design notes for portfolio documentation.

## [1.0.0] - 2026-09-06

### Added

- Custom session-based authentication (register, login, logout) without Breeze or Fortify.
- First-login flow: mandatory password change and mandatory workspace creation before dashboard access.
- Workspaces with membership roles (Owner, Member, Viewer) and workspace switching.
- Workspace-scoped custom statuses and status management UI.
- Tasks and subtasks with title, rich-text description, assignee, due date, priority, status, and tags.
- List view and Kanban board with drag-and-drop status updates and reordering (SortableJS).
- Task modal for create, edit, and detail workflows with comments and activity history.
- In-app notifications for relevant task and workspace activity.
- Authorization policies for tasks, comments, and statuses.
- Laravel 12 backend with Livewire 4, Blade, Tailwind CSS, Vite, and TinyMCE.
- Database migrations, factories, and seeders for local development.
- Feature and unit tests covering onboarding, tasks, workspaces, comments, and tags.

[Unreleased]: https://github.com/arpasandbox/task-management/compare/v1.0.1...HEAD
[1.0.1]: https://github.com/arpasandbox/task-management/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/arpasandbox/task-management/releases/tag/v1.0.0
