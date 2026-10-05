---
name: laravel-team-agent
description: Develop Laravel projects using strict MVC, Service Interfaces, simple readable code, exact user scope, multi-member code protection, and automatic shared project history through a Google Drive synchronized folder.
---

# Antigravity Laravel Team Development Skill

This skill combines the Laravel development rules with the team's shared project-memory workflow.

The Agent must:
- follow the exact user requirement;
- use the project's required Laravel MVC architecture and Service Interfaces;
- protect other members' work;
- automatically load shared project history before substantial coding;
- use the local Google Drive synchronized folder as shared memory;
- verify changes and update shared history after meaningful work.

# Antigravity Laravel Development Skill

## 1. Purpose

This skill defines the mandatory working rules for an Antigravity coding agent working on a Laravel project.

The agent must:

- Follow standard Laravel MVC architecture.
- Use Service Interfaces as the contract between Controllers and Services.
- Keep code hard-coded, explicit, simple, readable, and easy to maintain.
- Implement only the exact requirement requested by the user.
- Avoid unnecessary abstraction, creativity, refactoring, or feature expansion.
- Protect other developers' work.
- Maintain persistent shared conversation and development history through the Google Drive folder `history-conversation-agent`.
- Continue previous team members' work from the latest verified project state.
- Verify and automatically fix errors when possible.
- Follow an image supplied by the user exactly when the image describes UI, layout, behavior, or implementation requirements.
- Write code comments in Vietnamese.

The agent must prioritize correctness, scope control, maintainability, and continuity over cleverness.

---

# 2. Core Development Principles

## 2.1 Exact Requirement Only

The agent must do exactly what the user asks.

Do not:

- Add features that were not requested.
- Add optional improvements without being asked.
- Refactor unrelated code.
- Rename unrelated classes, methods, variables, routes, columns, or files.
- Change architecture without a requirement.
- Introduce new design patterns without a requirement.
- Replace an existing implementation merely because another implementation appears cleaner.
- Modify another developer's work unless the user's task explicitly requires it.
- Guess missing business rules.
- Invent database columns, routes, models, APIs, permissions, or workflows.

If a requirement is unclear and the ambiguity materially affects implementation, inspect the existing project and shared history first. Ask the user only when the required behavior cannot be determined safely.

---

## 2.2 Simple and Explicit Code

Code must be:

- Hard-coded where appropriate.
- Explicit.
- Easy to read.
- Easy to debug.
- Easy to maintain.
- Easy for another developer to continue.

Prefer:

```php
if ($trip->status === 'completed') {
    return true;
}

return false;
```

over unnecessarily compressed or clever expressions.

Do not optimize for fewer lines.

Optimize for clear business logic.

---

## 2.3 No Abbreviation

Do not use abbreviations in:

- Class names.
- Method names.
- Variable names.
- Interface names.
- Database-related code.
- Business logic identifiers.

Use full descriptive English names.

Prefer:

```php
$destinationManagementService
```

over:

```php
$destinationMgmtService
```

Prefer:

```php
calculateRemainingBudget()
```

over:

```php
calcRemainBudget()
```

---

## 2.4 Code Comments

All code comments written by the agent must be in Vietnamese.

Comments should explain business or technical intent when useful.

Do not add comments that merely repeat obvious code.

Example:

```php
// Kiểm tra ngày đặt phòng phải nằm trong thời gian của chuyến đi.
if ($bookingDate < $trip->start_date || $bookingDate > $trip->end_date) {
    throw ValidationException::withMessages([
        'booking_date' => 'Ngày đặt phòng không nằm trong thời gian chuyến đi.',
    ]);
}
```

---

# 3. Laravel MVC Architecture

Use standard Laravel MVC architecture.

Required flow:

```text
Route
    ↓
Controller
    ↓
Form Request / Policy
    ↓
Service Interface
    ↓
Service
    ↓
Model
    ↓
Database
    ↓
Service
    ↓
Controller
    ↓
Blade View / Redirect / JSON
```

The normal application flow is:

```text
Controller
    ↓
Service Interface
    ↓
Service
    ↓
Model
```

---

## 3.1 Controller Responsibility

Controllers must remain thin.

Controllers may handle:

- HTTP requests.
- Form Request validation.
- Authorization.
- Calling Service Interfaces.
- Returning views.
- Returning redirects.
- Returning JSON responses.

Controllers must not contain complex business logic.

Example:

```php
public function store(StoreTripRequest $request)
{
    $this->tripService->createTrip($request->validated());

    return redirect()
        ->route('trips.index')
        ->with('success', 'Tạo chuyến đi thành công.');
}
```

Business rules belong in the Service.

---

## 3.2 Service Responsibility

Services contain business logic.

A Service must:

- Implement its corresponding Service Interface.
- Execute business rules.
- Coordinate Models.
- Handle transactions when required.
- Validate business rules that are not HTTP validation.
- Return appropriate business results.
- Explicitly handle expected business errors.

Do not move HTTP-specific concerns into Services.

---

## 3.3 Model Responsibility

Models are responsible for:

- Eloquent relationships.
- Casts.
- Scopes.
- Persistence-related behavior.
- Model-level data behavior.

Do not place application business workflows inside Models when they belong to a Service.

Use Eloquent relationships instead of unnecessary manual queries.

---

## 3.4 Policy Responsibility

Use Policies for authorization.

Required Policies:

- `TripPolicy`
- `ItineraryPolicy`
- `ExpensePolicy`
- `ReviewPolicy`
- `BookingPolicy`

Authorization must happen before protected modifications.

---

## 3.5 Form Request Responsibility

Use Form Requests for request validation.

Form Requests should handle HTTP input validation.

Do not duplicate the same validation unnecessarily between Controller and Service.

Business validation that depends on application state may remain in the Service.

---

# 4. Interfaces

The following Service Interfaces are the approved interfaces for this project.

Do not create another interface for a requirement that can use one of these interfaces.

```text
AuthServiceInterface
ProfileServiceInterface
DestinationServiceInterface
FavoriteServiceInterface
TripServiceInterface
ItineraryServiceInterface
BudgetServiceInterface
ExpenseServiceInterface
ReviewServiceInterface
BookingServiceInterface
PaymentServiceInterface
RoomTypeServiceInterface
CityServiceInterface
CategoryServiceInterface
DestinationManagementServiceInterface
DestinationImageServiceInterface
RoomTypeManagementServiceInterface
ReviewManagementServiceInterface
UserManagementServiceInterface
DashboardServiceInterface
ActivityLogServiceInterface
AiTravelAssistantInterface
```

Services must implement their corresponding interfaces.

Example:

```php
class TripService implements TripServiceInterface
{
    // ...
}
```

Controllers must depend on the interface rather than directly coupling to the concrete Service whenever dependency injection is used.

Example:

```php
public function __construct(
    private TripServiceInterface $tripService
) {
}
```

Interfaces define the contract.

Services contain the implementation.

---

# 5. Controllers

## User Controllers

Approved controllers:

```text
AuthController
ProfileController
HomeController
DestinationController
FavoriteController
TripController
ItineraryController
BudgetController
ExpenseController
ReviewController
BookingController
PaymentController
```

## Admin Controllers

Approved admin controllers:

```text
Admin\DashboardController
Admin\UserController
Admin\CityController
Admin\CategoryController
Admin\DestinationController
Admin\DestinationImageController
Admin\RoomTypeController
Admin\ReviewController
Admin\BookingController
Admin\PaymentController
Admin\ActivityLogController
```

Do not create alternative controllers for the same responsibility without a clear requirement.

---

# 6. Service Methods

Use the following approved business methods where applicable.

## Authentication

```text
registerUser()
authenticate()
logoutUser()
refreshCaptcha()
sendPasswordResetLink()
resetPassword()
verifyEmail()
resendVerificationEmail()
handleSocialLogin()
```

## Profile

```text
getProfile()
updateProfile()
updateAvatar()
changePassword()
```

## Destination

```text
getDestinations()
searchDestinations()
getFeaturedDestinations()
getDestinationDetails()
getRelatedDestinations()
getDestinationGallery()
```

## Favorite

```text
getFavorites()
addFavorite()
removeFavorite()
addFavoriteToTrip()
```

## Trip

```text
getUserTrips()
createTrip()
getTripDetails()
updateTrip()
deleteTrip()
updateTripStatus()
validateTripDates()
getTripSummary()
cloneTrip()
reopenTrip()
handleTripDateChange()
```

## Itinerary

```text
getItinerary()
createItineraryItem()
updateItineraryItem()
deleteItineraryItem()
validateItemWithinTripDates()
hasTimeOverlap()
reorderItineraryItems()
linkBookingToItinerary()
```

## Budget

```text
getBudgetSummary()
updateBudget()
calculateTotalExpenses()
calculateRemainingBudget()
calculateOverBudgetAmount()
isOverBudget()
```

## Expense

```text
getExpenses()
createExpense()
updateExpense()
deleteExpense()
```

## Review

```text
getApprovedReviews()
canCreateReview()
createReview()
updateReview()
deleteReview()
recalculateDestinationRating()
approveReview()
hideReview()
```

## Booking

```text
createBooking()
getUserBookings()
getBookingDetails()
cancelBooking()
validateBookingDates()
checkRoomAvailability()
calculateBookingTotal()
generateBookingCode()
updateBookingStatus()
```

## Payment

```text
getPaymentDetails()
createPayment()
validatePaymentAmount()
processPayment()
handlePaymentCallback()
updatePaymentStatus()
```

Do not invent alternative names when an approved method already represents the requested operation.

---

# 7. CRUD Rules

Use standard Laravel CRUD method names:

```text
index()
create()
store()
show()
edit()
update()
destroy()
```

Do not replace standard CRUD method names with custom names when the operation is standard CRUD.

Example:

```php
public function index()
{
    // ...
}

public function create()
{
    // ...
}

public function store(StoreDestinationRequest $request)
{
    // ...
}

public function show(Destination $destination)
{
    // ...
}

public function edit(Destination $destination)
{
    // ...
}

public function update(
    UpdateDestinationRequest $request,
    Destination $destination
) {
    // ...
}

public function destroy(Destination $destination)
{
    // ...
}
```

---

# 8. Naming Rules

Use:

```text
Interface
    PascalCase + Interface

Service
    PascalCase + Service

Controller
    PascalCase + Controller

Model
    Singular PascalCase

Method
    camelCase

Variable
    camelCase

Form Request
    Store/Update + Entity + Request

Policy
    Entity + Policy

Blade View
    lowercase-kebab-case

Route Name
    resource.action
```

All code identifiers must use English.

Use full words.

---

# 9. Coding Style

The agent must:

- Prefer explicit code.
- Prefer simple `if/else` logic when clearer.
- Keep each method focused on one responsibility.
- Validate input before business processing.
- Authorize before protected modifications.
- Use dependency injection.
- Bind Interfaces to Services in the Laravel service container.
- Use Eloquent relationships.
- Use transactions when multiple database operations must succeed or fail together.
- Handle expected business errors explicitly.
- Never silently ignore exceptions.
- Return the correct Laravel response type.
- Keep business logic out of Blade, Routes, and Controllers.
- Avoid unnecessary helpers.
- Avoid unnecessary repositories.
- Avoid unnecessary factories.
- Avoid unnecessary design patterns.
- Avoid over-engineering.

---

# 10. Scope Protection and Multi-Developer Safety

Multiple developers may work on the same project.

The agent must protect other developers' work.

Before modifying code:

1. Identify exactly which files are required by the user's task.
2. Inspect recent changes.
3. Check shared history.
4. Check the current Git/project state when available.
5. Identify files being actively modified by another developer.
6. Determine whether the requested change conflicts with another developer's work.
7. Modify only the smallest necessary scope.

## Do Not Modify Other Work

Do not:

- Rewrite unrelated files.
- Reformat unrelated code.
- Rename unrelated variables.
- Refactor unrelated methods.
- Reorganize directories without requirement.
- Change another developer's feature.
- Delete code merely because it appears unused.
- Change shared architecture without requirement.

## Conflict Blocking Rule

If implementing the requested task would likely overwrite, break, or materially alter another developer's active work:

1. Stop before making the conflicting change.
2. Identify the conflict.
3. Inspect shared history and actual source code.
4. Determine whether the task can be implemented without touching the conflicting area.
5. If it cannot, report the conflict to the user instead of silently overwriting the other developer's work.

The agent must prioritize preservation of existing team work.

---

# 11. User-Supplied Images

If the user supplies an image as a requirement, the image is part of the specification.

The agent must:

- Inspect the image carefully.
- Reproduce the requested UI/layout/behavior shown in the image.
- Match visible structure, placement, labels, controls, spacing, and relevant visual behavior as closely as required.
- Avoid inventing additional UI elements.
- Avoid replacing the design with a preferred design.

If the image conflicts with existing code, determine the smallest required change.

Do not claim that an image requirement is implemented unless the result was actually checked.

---

# 12. Error Handling and Self-Repair

When an implementation produces an error:

1. Read the complete error.
2. Identify the actual cause.
3. Inspect the related code and configuration.
4. Fix the root cause.
5. Run the relevant verification again.
6. Repeat when the next error is directly caused by the same task.
7. Stop if fixing the error would require changing unrelated functionality or another developer's work.

The agent should automatically fix errors that are clearly within the requested scope.

Do not hide errors.

Do not claim success when verification failed.

If verification cannot be completed, explicitly record:

```text
Status: Completed - Not Fully Verified
```

---

# 13. Verification

Before considering a task complete, verify whenever possible.

Verification can include:

```text
Unit tests
Integration tests
Feature tests
Build
Application startup
Runtime verification
Static analysis
Command output
Manual inspection
```

Never claim:

```text
Tests passed
Build succeeded
Feature works
```

unless the result was actually verified.

---

# Shared Memory Integration

The shared-memory rules below are part of the same skill and are mandatory.

For this project, shared history is accessed through the locally synchronized
Google Drive folder `history-conversation-agent`.

Do not use Google Drive MCP, Google Drive API, OAuth credentials, Google Cloud
credentials, or browser automation for shared history.

Git remains responsible for source-code collaboration and version history.
The shared Google Drive folder is responsible for shared project context,
decisions, session continuity, and development history.


## 1. Purpose

This skill defines the shared project-memory workflow for the team.

The shared memory is stored in a Google Drive folder named:

history-conversation-agent

Google Drive is used as a shared filesystem through Google Drive for desktop.

The Agent must use the local synchronized folder as shared memory. The Agent must not depend on Google Drive MCP, Google OAuth Client, Google Cloud credentials, or Drive API.

The source code of the project remains the source of truth for implementation. The shared history is the source of truth for project context, previous work, decisions, and session continuity.

---

## 2. Shared Memory Structure

The shared folder must use this structure:

history-conversation-agent/
├── CURRENT_STATE.md
├── PROJECT_TREE.md
├── WORKING_HISTORY.md
├── PROMPT_HISTORY.md
├── CHANGE_HISTORY.md
└── workdays/
    ├── YYYY-MM-DD/
    │   ├── MEMBER_A/
    │   │   ├── SESSION_001.md
    │   │   ├── SESSION_002.md
    │   │   └── ...
    │   ├── MEMBER_B/
    │   │   └── SESSION_001.md
    │   └── ...
    └── ...

---

## 3. Finding the Shared Folder

Never hard-code a Windows user-specific path.

Different members may have different local paths, for example:

C:\Users\<member>\Google Drive\My Drive\history-conversation-agent

or another Google Drive for desktop path.

At the beginning of every session:

1. Search the accessible local filesystem for a folder named:
   history-conversation-agent
2. Prefer the folder that is inside a Google Drive synchronized location.
3. Verify that the folder contains the shared memory files or can be initialized with them.
4. Use the discovered absolute path only for the current session.
5. Never store a member's personal absolute path in shared memory.

If the folder cannot be found:

- Do not silently create a replacement folder somewhere else.
- Tell the user that the shared Google Drive folder is not available locally.
- Ask the user to make sure Google Drive for desktop is running and the shared folder is available.

---

## 4. Session Startup Protocol

Every new coding session must begin by loading shared project context before making changes.

Read in this order:

1. CURRENT_STATE.md
2. PROJECT_TREE.md
3. CHANGE_HISTORY.md
4. WORKING_HISTORY.md
5. The most recent relevant session files under:
   workdays/YYYY-MM-DD/<MEMBER>/

If today's session directory exists, read the latest sessions first.

If the current task clearly belongs to previous work, read the relevant older session before coding.

Do not read every historical session by default.

Use the most recent shared state first and expand into older history only when necessary.

---

## 5. Continuity Between Members

When a new member starts working:

1. Find history-conversation-agent.
2. Read CURRENT_STATE.md.
3. Read PROJECT_TREE.md.
4. Read recent CHANGE_HISTORY.md.
5. Read relevant WORKING_HISTORY.md entries.
6. Read the latest relevant session.
7. Inspect the actual source code.
8. Compare the history with the current source code.
9. Treat the source code as the final authority for what currently exists.
10. Continue from the actual current state.

The Agent must not ask the new member to manually explain previous work when the required information already exists in shared memory.

Example:

Member A stops after implementing authentication.

Member B starts with:

"Continue the authentication work."

The Agent should read the shared history, inspect the current project, understand what Member A completed, identify unfinished work, and continue.

---

## 6. Source of Truth

Use this priority:

1. Current source code
2. Current project configuration
3. CURRENT_STATE.md
4. PROJECT_TREE.md
5. CHANGE_HISTORY.md
6. WORKING_HISTORY.md
7. PROMPT_HISTORY.md
8. Historical SESSION files

History describes what was done and why.

The actual project files determine what currently exists.

If history conflicts with source code, inspect the source code and record the correction in shared history when appropriate.

Never blindly restore old code because an old session says it existed.

---

## 7. Before Coding

Before modifying the project:

1. Understand the user's current task.
2. Load relevant shared memory.
3. Inspect the current source code.
4. Identify the exact files involved.
5. Determine existing architecture and conventions.
6. Avoid unnecessary redesign.
7. Do not introduce new architecture unless the task requires it.
8. Preserve existing business logic unless the user explicitly asks to change it.

The Agent must work on the existing project, not create a parallel implementation.

---

## 8. During Coding

While working:

- Make only the changes required by the task.
- Follow the existing project architecture.
- Keep code readable and maintainable.
- Do not introduce unnecessary abstractions.
- Do not rename unrelated files or classes.
- Do not rewrite unrelated code.
- Do not remove existing functionality without a reason.
- Keep changes consistent with the project's current conventions.

If the user specifies an architecture, follow it exactly.

---

## 9. Verification

After implementation:

1. Check changed files.
2. Run the relevant tests or build commands when available.
3. Verify the requested behavior.
4. Fix errors caused by the implementation.
5. Do not claim success when verification has not been performed.
6. Record important verification results in the session history.

---

## 10. Updating Shared Memory

At the end of every meaningful coding session, update the shared memory.

Update:

### CURRENT_STATE.md

Keep the current project state concise.

Include:

- Current objective
- Current implementation status
- Completed work
- In-progress work
- Known problems
- Next recommended action

### PROJECT_TREE.md

Keep the important project structure current.

Do not record every generated file unless it is relevant.

Represent the project as a readable tree.

### CHANGE_HISTORY.md

Record meaningful code changes.

For each change include:

- Date
- Member
- Area
- What changed
- Why it changed
- Verification status

### WORKING_HISTORY.md

Record important decisions and project-level progress.

Include:

- Decisions
- Architecture decisions
- Important discoveries
- Unfinished work
- Dependencies
- Risks

### PROMPT_HISTORY.md

Record meaningful user requests that affected the project.

Include:

- Date
- Member
- Request
- Result
- Status

Do not store unnecessary conversational text.

---

## 11. Session Files

Create one session file for each meaningful coding session.

Path:

workdays/YYYY-MM-DD/<MEMBER>/SESSION_NNN.md

Example:

workdays/2026-10-05/MEMBER_B/SESSION_001.md

Each session must contain:

# Session

Date:
Member:
Session:

## Objective

What the member wanted to accomplish.

## Context Loaded

Which shared-memory files were read.

## Work Performed

What was actually changed.

## Files Changed

List changed files.

## Verification

Tests, build, commands, or manual verification performed.

## Problems

Problems encountered.

## Decisions

Important decisions made during the session.

## Current Status

What is now complete and what remains.

## Next Step

The next useful action for another member.

---

## 12. Session Numbering

Session numbering is per member and per day.

Example:

workdays/
└── 2026-10-05/
    ├── MEMBER_A/
    │   ├── SESSION_001.md
    │   └── SESSION_002.md
    └── MEMBER_B/
        └── SESSION_001.md

Never overwrite an existing session.

Before creating a session file:

1. Check existing SESSION_NNN.md files.
2. Use the next available number.

---

## 13. Concurrency

Multiple team members may work on the project.

Before writing shared memory:

1. Read the latest shared files again if another member may have worked recently.
2. Do not overwrite newer information blindly.
3. Preserve existing entries.
4. Append new historical information when possible.
5. Update current-state sections carefully.

Source-code synchronization remains the responsibility of the team's Git workflow.

Google Drive history does not replace Git.

---

## 14. Google Drive Synchronization Rules

The Agent interacts with the local synchronized folder only.

Do not use:

- Google Drive API
- Google Drive MCP
- OAuth credentials
- Client secrets
- Google Cloud credentials
- Browser-based Google Drive automation

The Agent should treat the synchronized folder like a normal local directory.

Google Drive for desktop handles synchronization between members.

If synchronization appears delayed:

- Do not assume another member's changes do not exist.
- Check the local folder.
- If necessary, tell the user to wait for Google Drive synchronization to complete before continuing.

---

## 15. Shared Folder Permissions

The shared folder must be shared with team members.

If the Agent must write history files, members need permission to edit the shared folder.

Recommended model:

- Team members: Editor
- Shared history folder: shared with the project team
- Source code: managed by Git
- Shared memory: managed by this skill

Do not share Google account passwords, OAuth client secrets, or private credentials.

---

## 16. History Quality Rules

Shared memory must remain useful.

Do not store:

- Full repetitive conversations
- Unrelated chat
- Temporary debugging output with no future value
- Secrets
- Passwords
- API keys
- OAuth client secrets
- Access tokens

Store:

- Important requests
- Implementation decisions
- Architecture decisions
- Changes
- Verification results
- Problems
- Current state
- Next steps

Keep shared files concise enough that another Agent can understand the project quickly.

---

## 17. Automatic Continuity Rule

The core rule is:

A member must be able to open the project and start working without manually explaining the previous member's work.

The Agent must use:

Google Drive synchronized shared memory
+
current source code
+
Git project state

to reconstruct the current working context.

The Agent must then continue the project from its actual current state.

---

## 18. End-of-Session Checklist

Before finishing a meaningful coding session:

- [ ] Implementation completed or current status identified
- [ ] Relevant tests/build verification performed
- [ ] CURRENT_STATE.md updated
- [ ] PROJECT_TREE.md updated if structure changed
- [ ] CHANGE_HISTORY.md updated
- [ ] WORKING_HISTORY.md updated when a decision or important progress occurred
- [ ] PROMPT_HISTORY.md updated when the request materially affected the project
- [ ] SESSION_NNN.md created
- [ ] No secrets written to shared memory
- [ ] Next step recorded

---

## 19. First-Time Initialization

If history-conversation-agent exists but is empty, initialize:

history-conversation-agent/
├── CURRENT_STATE.md
├── PROJECT_TREE.md
├── WORKING_HISTORY.md
├── PROMPT_HISTORY.md
├── CHANGE_HISTORY.md
└── workdays/

Do not initialize a second shared-memory folder if an existing synchronized folder can be found.

---

## 20. Final Principle

Keep the system simple.

Google Drive provides shared storage and synchronization.

Git provides source-code history and collaboration.

SKILL.md defines Agent behavior.

The Agent reads shared memory at startup, works on the real project, verifies changes, and writes the new state back to the shared folder.

No Google Drive MCP is required.
No Google OAuth integration is required.
No Google Cloud credentials are required.
