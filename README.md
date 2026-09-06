# Task Management System (ClickUp-style)

## Project Overview

A full-stack task management application built as a personal portfolio project, inspired by platforms such as ClickUp. The project was created to demonstrate practical, unrestricted Laravel development skills outside of client work, where previous professional projects are subject to NDAs.

The application focuses on real-world project and task management workflows, including hierarchical organization, permissions, task collaboration, notifications, and an interactive Kanban experience.

## My Role

**Sole developer.** I designed the database architecture, built the backend and frontend, and implemented the entire feature set independently.

This included authentication, authorization and permissions, workspace and project management, task workflows, collaboration features, notifications, and the drag-and-drop Kanban interface.

## Features

- **Hierarchical organization:** Workspaces → Projects → Tasks → Subtasks
- **Task management:** Title, description, assignee, due date, priority, status, and tags
- **Multiple views:** List view and interactive Kanban board
- **Drag-and-drop:** Reorder and update task status directly from the Kanban board
- **Task collaboration:** Comments and activity history per task
- **Notifications:** In-app notifications for relevant task and project activity
- **Role-based permissions:** Owner / Member / Viewer
- **Custom authentication flow:** Register → automatic login → mandatory first-login password change → mandatory first workspace creation → dashboard
- **Workspace-based access control:** Permissions and visibility are handled according to the user's role within each workspace

## Tech Stack

- **Backend:** Laravel with session-based authentication
- **Frontend:** Blade, Livewire, jQuery, Tailwind CSS
- **Drag-and-drop:** SortableJS
- **Database:** MySQL
- **Authentication:** Custom Laravel authentication flow without Breeze or Fortify scaffolding

## Notes

Authentication was intentionally implemented from the ground up rather than relying on Laravel starter kits such as Breeze or Fortify. This was a deliberate decision to demonstrate a practical understanding of Laravel's authentication and authorization mechanisms, session handling, middleware, validation, and application flow.

The application uses **session-based authentication** rather than Sanctum because it is designed as a single-domain application without a separate API layer. This keeps the architecture appropriate to the project's requirements while avoiding unnecessary complexity.

The project was also designed to demonstrate how Laravel can be used to build a complete, production-style application from the database layer through to the interactive frontend, rather than functioning as a simple CRUD demonstration.
