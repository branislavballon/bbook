# Assignment – Mini Social Network (Facebook clone)

For interview purposes
### assignment
**Build your own X (Twitter) or Facebook network using AI agentic coding.**

**Document your decisions, development plan, and commit history, and share it with us in your own git repository.**

**Estimated time: approx. 2.5 hours.**
## Goal

Build a simple social network inspired by Facebook, with an emphasis on clean architecture, reusable components, and a solid development process. The application will be implemented using AI agentic coding and the whole process will be documented as required.

---

# Technologies

- Laravel 13

- **Laravel 13 React starter kit** (official kit; the official starter kits replaced Breeze/Jetstream as of Laravel 12)

- Inertia.js 3

- React

- TypeScript

- Tailwind CSS

- shadcn/ui (components ship with the kit – Button, Input, Card, Avatar, etc.)

- SQLite (for the assignment)

---

# Design

- minimalist design

- based on the starter template

- emphasis on simplicity

- reusable components built incrementally from the start (not refactored at the end)

---

# Authentication

An unauthenticated user can:

- register

- log in

All other parts of the application are protected by an Auth Guard and are available only to authenticated users. Authentication is part of the starter kit.

---

# Functionality

## Authenticated user

A user can:

- edit their profile (including changing their name)

- create a new post

- view the feed (own posts + friends' posts)

- view a post detail

- edit their own post

- delete their own post

- send a friend request

- accept or reject a friend request

- view their list of friends

---

## Posts

Each post contains:

- author

- text

- creation date

- number of likes

- number of comments

> **Decision:** the like and comment counts are not denormalized into columns; they are computed on query via `withCount('likes', 'comments')`. Fewer synchronization bugs, simpler for the MVP.

---

## Likes

An authenticated user can:

- like other users' posts

- remove their like

---

## Comments

An authenticated user can:

- add comments to posts

- view comments

Editing and deleting comments is not part of the MVP.

---

## Friends

An authenticated user can:

- send a friend request to another user

- accept or reject a received request

- view the list of received (pending) requests

- view their list of friends

Friendship is mutual – once a request is accepted, both users are friends.

**Feed:** the feed shows the user's own posts and their friends' posts (not posts from all users).

> **Model:** `Friendship` with a `user_id` / `friend_id` pair and a status (`pending` / `accepted`). The status handles both requests and confirmed friendships in a single table. Rejection deletes the request (or sets status `declined`).

---

## User profile

A profile contains:

- name

- avatar (generated from initials – starter kit default, no file upload)

- list of own posts

> **Decision:** for the MVP the avatar is generated from the user's initials (starter kit default). Image upload is intentionally deferred – this avoids storage, validation, and unnecessary overhead within the time limit.

---

# Navigation

- Login

- Registration

- Home page (feed)

- Post detail

- User profile

- Friends (friends list + pending requests)

---

# Architecture

The project is split into small, reusable components built incrementally from the start.

Examples:

- Button

- Input

- Card

- Avatar

- Post Card

- Comment Item

- Like Button

- Layout

- Header

- Navigation

The goal is to prepare the architecture so components can be easily extended.

## Authorization

- Access to all protected parts is handled by the Auth Guard.

- Ownership (editing and deleting only one's own post) is handled by a **Laravel Policy** (`PostPolicy` – `update`, `delete`), not just a check in the controller.

- Friend requests: only the recipient can accept/reject a request – handled by **`FriendshipPolicy`** (`respond`).

- The feed returns only posts by the authenticated user and their friends (scope on the `Post` query).

## Validation

- Inputs are validated via **Form Requests** (e.g. `StorePostRequest`, `UpdatePostRequest`), not inline in the controller.

## Demo data

- **Factories + seeder** for users, posts, likes, and comments, so the feed is not empty during the demo.

---

# Tests

The assignment emphasizes a solid development process, so the plan includes targeted **Pest feature tests** for key paths:

- registration / login

- creating a post by an authenticated user

- `PostPolicy` – a user cannot delete another user's post

If the tests do not fit within the time limit, they will be explicitly listed in the documentation as out of scope, together with a proposal for how they would be implemented (no silent omission).

---

# MVP

The MVP contains only the basic functionality:

- registration

- login

- profile

- creating posts

- editing posts

- deleting posts

- post feed (own + friends' posts)

- likes

- comments

- friends (requests, confirmation, friends list)

---

# Not to implement for now

The following features will not be part of the MVP:

- Messenger / chat

- Sharing posts

- User search (user discovery for the MVP is handled by a simple user list, not full-text search)

- Groups

- Pages

- Reels

- Stories

- Video and image upload (including avatars – the MVP uses initials)

- Administration

---

# Future extensions

Possible features for later versions:

- notifications

- chat

- search

- realtime updates

- infinite scroll

- user tagging

- hashtags

- bookmarks

- dark mode

- upload and custom avatars

---

# Open questions

To be decided before implementation:

- how to implement notifications

- how to implement chat

- realtime communication vs. polling

- pagination strategy (pagination vs. infinite scroll)

- future architecture for realtime functionality

> Note: the "image upload" and "avatar" questions are resolved for the MVP – upload is deferred, the avatar is generated from initials.

---

# Implementation priority

1. Project initialization (Laravel 13 React starter kit)

2. Authentication (part of the kit, verification and adjustments)

3. Layout + basic reusable components

4. Post CRUD (including `PostPolicy` and Form Requests)

5. Friends (`Friendship` model, requests, confirmation, `FriendshipPolicy`)

6. Feed, limited to own posts + friends' posts

   > **Reordered:** friends now come before the feed. Posts are visible only to the author and their friends ([ADR-0001](adr/0001-posts-are-visible-to-friends-only.md)), so the visibility scope the feed is built on cannot exist until the `Friendship` model does. Building the feed first would mean writing it twice.

7. Likes

8. Comments

9. User profile

10. Incremental cleanup and consolidation of components

11. Factories + seeder and (time permitting) Pest feature tests

12. Documentation and commit history

13. Project finalization

---

# Note

Throughout implementation, the following will be documented on an ongoing basis:

- architectural decisions (mini-ADRs in `docs/adr/`)

- the domain glossary (`CONTEXT.md`), kept current as terms are settled

- the development plan and its progress (a spec in `docs/specs/`, tickets with a `Status:` line in `docs/tickets/`)

- reasons for individual solutions

- commit history (conventional commits – feat/fix/refactor – for a readable history)

> **Decision:** no verbatim log of AI prompts is kept. The process record is the spec, the tickets and their `Status:` transitions, the ADRs, and the conventional commit history — these show the reasoning and the order it happened in, which a raw transcript buries rather than reveals.

The project will be published in its own Git repository as required.
