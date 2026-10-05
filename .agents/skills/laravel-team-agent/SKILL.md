---
name: laravel-team-agent
description: Develop Laravel projects using strict MVC, Service Interfaces, simple readable code, scope protection, and shared Google Drive development history.
---

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

# 14. Shared History / Persistent Working Memory

The project uses a shared Google Drive folder:

```text
history-conversation-agent
```

This folder is the team's persistent working memory.

It allows different developers and Antigravity sessions to continue the same project without manually explaining previous work.

The shared history tracks:

- Team members.
- Antigravity sessions.
- Working days.
- Prompts.
- Code changes.
- Project architecture.
- Decisions.
- Unfinished tasks.
- Problems.
- Solutions.

The history is not merely a conversation archive.

It is shared project working memory.

---

# 15. Google Drive Rule

The agent must use the Google Drive MCP connection to access:

```text
history-conversation-agent
```

Do not assume this folder exists on the local filesystem.

Do not create a replacement folder with a similar name.

If the folder cannot be found:

1. Search Google Drive again.
2. Verify the exact folder name.
3. If it still cannot be found, report the problem.
4. Do not silently create another folder.

---

# 16. Shared Memory Structure

The expected structure is:

```text
history-conversation-agent/
│
├── CURRENT_STATE.md
├── PROJECT_TREE.md
├── WORKING_HISTORY.md
├── PROMPT_HISTORY.md
├── CHANGE_HISTORY.md
│
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
```

There are two levels:

```text
Current Memory
    ↓
Root Markdown files

Historical Memory
    ↓
workdays/YYYY-MM-DD/
```

---

# 17. CURRENT_STATE.md

`CURRENT_STATE.md` is the primary shared memory file.

Read it at the beginning of every new working session.

It must represent the latest verified project state.

It should contain:

- Project name.
- Project objective.
- Current development status.
- Current active task.
- Completed tasks.
- Pending tasks.
- Known problems.
- Known limitations.
- Important decisions.
- Important files.
- Current blockers.
- Next recommended action.
- Last updated date.
- Last updated member.

Keep it concise and actionable.

It must not become a full conversation transcript.

After meaningful work, update it to reflect the latest verified state.

---

# 18. PROJECT_TREE.md

`PROJECT_TREE.md` describes the current project structure and architecture.

It may contain:

1. File and directory structure.
2. Logical architecture.
3. Important components.
4. Responsibilities of important components.

Update it when:

- Directories are added.
- Directories are removed.
- Important files are added.
- Important files are removed.
- Architecture changes.
- Major project structure changes.

Do not regenerate it for insignificant code changes.

For this Laravel project, architecture should reflect the actual project structure and the MVC + Interface + Service approach.

---

# 19. WORKING_HISTORY.md

`WORKING_HISTORY.md` contains development history that is still relevant to the current project.

It should contain:

- Current work.
- Previous relevant work.
- Important decisions.
- Investigations.
- Rejected approaches.
- Unresolved problems.
- Attempted solutions.
- Current direction.
- Next steps.

Do not copy every prompt into this file.

Keep it focused on meaningful development context.

---

# 20. PROMPT_HISTORY.md

`PROMPT_HISTORY.md` records meaningful project-related prompts.

Do not record:

- Greetings.
- Casual conversation.
- Unrelated questions.
- Simple acknowledgements.
- Prompts with no project impact.

Each meaningful prompt should record:

```text
Date and time
Member
Original prompt
Intent
Result
Status
```

Preserve the user's original wording accurately.

Do not fabricate prompts.

Do not substantially change the meaning of the original prompt.

---

# 21. CHANGE_HISTORY.md

`CHANGE_HISTORY.md` records meaningful project changes.

Each entry should contain:

```text
Timestamp
Member
Change
Reason
Files Added
Files Modified
Files Deleted
Result
Verification
```

Only record changes that actually occurred.

Never invent:

- Files.
- Code changes.
- Tests.
- Decisions.
- Results.
- Timestamps.

If a task fails, record the failure.

If a task is incomplete, record it as incomplete.

Do not record insignificant formatting-only changes.

---

# 22. Daily Workday History

Detailed working sessions must be stored under:

```text
workdays/YYYY-MM-DD/
```

The date format must always be:

```text
YYYY-MM-DD
```

Example:

```text
workdays/2026-10-05/
```

Do not use:

```text
October 5
05-10-2026
Oct-05
2026_10_05
```

---

# 23. Workday Creation

At the beginning of a session:

1. Determine the current date.
2. Check whether `workdays/YYYY-MM-DD/` exists.
3. Create it only if it does not exist.
4. Never create duplicate workday folders.

---

# 24. Member Folder

Use:

```text
workdays/YYYY-MM-DD/<MEMBER>/
```

If member identity is known, use the known member name.

If it cannot be determined, use:

```text
workdays/YYYY-MM-DD/UNKNOWN/
```

Never guess a member identity.

---

# 25. Session Files

Each working session must have a unique Markdown file.

Example:

```text
workdays/2026-10-05/MEMBER_A/SESSION_001.md
```

The next session is:

```text
SESSION_002.md
```

Never overwrite an existing session.

Before creating a session:

1. Inspect the member folder.
2. Find existing session numbers.
3. Select the next available number.

---

# 26. Session File Format

A session file should contain:

```markdown
# Agent Working Session

## Session Information

- Date:
- Member:
- Session:
- Start:
- End:

## Initial State

Description of the project state when the session started.

## Prompts

### Prompt 01

Original user prompt.

## Work Performed

- ...

## Files Changed

### Added

- ...

### Modified

- ...

### Deleted

- ...

## Tree Changes

```text
project/
├── ...
└── ...
```

## Decisions

- ...

## Problems

- ...

## Solutions

- ...

## Verification

### Tests

...

### Build

...

### Runtime

...

## Current Result

...

## Remaining Work

- ...

## Next Recommended Action

...
```

Session records are historical records.

After completion:

- Do not delete them.
- Do not replace them.
- Do not silently rewrite them.
- Preserve their sequence.

If correction is necessary, append a correction record.

---

# 27. Automatic Session Startup Protocol

When any team member starts using the Antigravity agent, the agent must automatically initialize the working session before starting substantial coding.

The member does not need to manually tell the agent to read the history.

The agent must automatically:

1. Locate the Google Drive folder `history-conversation-agent`.
2. Read the latest shared project memory.
3. Determine the current project state.
4. Identify the current working member when the identity is available.
5. Create the current workday folder if necessary.
6. Create the next session record for that member.
7. Check recent work by other members.
8. Check for unfinished tasks, known problems, decisions, and active conflicts.
9. Compare shared history with the actual source code.
10. Determine the exact task from the member's current prompt.
11. Identify the smallest set of files required for the task.
12. Check whether another member is currently working on the same files or functionality.
13. Block conflicting modifications when they could overwrite or break another member's work.
14. Only after these checks, begin coding.

The agent must not wait for the member to explicitly say:

```text
read history
load context
check Google Drive
continue previous work
```

These operations are part of the normal automatic session startup.

The mandatory startup flow is:

```text
MEMBER STARTS ANTIGRAVITY
        ↓
AUTOMATICALLY LOCATE
history-conversation-agent
        ↓
READ CURRENT_STATE.md
        ↓
READ WORKING_HISTORY.md
        ↓
READ PROJECT_TREE.md
        ↓
READ RECENT CHANGE_HISTORY.md
        ↓
CHECK RECENT WORKDAY SESSIONS
        ↓
CHECK CURRENT PROJECT SOURCE CODE
        ↓
COMPARE HISTORY WITH ACTUAL CODE
        ↓
IDENTIFY MEMBER'S TASK
        ↓
CHECK OTHER MEMBERS' ACTIVE WORK
        ↓
CHECK FOR CONFLICTS
        ↓
CREATE / CONTINUE SESSION RECORD
        ↓
PLAN ONLY THE REQUESTED WORK
        ↓
START CODING
```

If shared history cannot be accessed, the agent must not pretend that it has loaded the history.

If enough local project context exists, the agent may continue coding while clearly recording that shared-history synchronization is pending.

## 27.1 Mandatory First Actions

Before the first meaningful code change in a new session, the agent must complete:

```text
1. Locate shared history.
2. Load current state.
3. Load relevant working history.
4. Load project tree.
5. Load recent changes.
6. Load relevant recent sessions.
7. Inspect actual source code.
8. Check for developer conflicts.
9. Create or identify the current session.
10. Start implementation.
```

No substantial implementation should begin before these checks unless the user explicitly asks for an emergency or isolated operation where the history is not relevant.

## 27.2 Automatic Context Recovery

If the current prompt appears to continue previous work, the agent must automatically search the shared history for the relevant previous session.

Example:

```text
Member A:
"Implement booking cancellation."
        ↓
Agent implements part of the feature.
        ↓
History is saved.
        ↓
Member B:
"Continue the booking cancellation."
```

Member B should not need to explain what Member A did.

The agent must automatically find:

```text
CURRENT_STATE.md
        +
WORKING_HISTORY.md
        +
PROJECT_TREE.md
        +
CHANGE_HISTORY.md
        +
Relevant Member A session
```

Then continue from the latest verified state.

## 27.3 Automatic Conflict Check

Before coding, the agent must determine whether the requested files or functionality were recently changed by another member.

If another member is actively working on the same area:

```text
Do not immediately overwrite the work.
        ↓
Inspect the recent changes.
        ↓
Determine whether both changes can coexist.
        ↓
If possible, make the smallest non-conflicting change.
        ↓
If not possible, stop the conflicting modification.
```

The agent must protect the other member's work.

## 27.4 Automatic Session Record

When a member starts a new working session, the agent must create or continue:

```text
workdays/YYYY-MM-DD/<MEMBER>/SESSION_NNN.md
```

The session record must begin with the available initial project state.

It should not wait until the end of the session to create the session identity.

The final session content is completed after implementation and verification.

## 27.5 Automatic Transition to Coding

After the startup checks are complete:

```text
History loaded
        ↓
Current state understood
        ↓
Relevant previous work understood
        ↓
Actual source code checked
        ↓
Developer conflicts checked
        ↓
User requirement understood
        ↓
Coding begins
```

The agent should not repeatedly explain this startup process to the member.

It should simply perform it.

---

# 29. Session Startup Protocol

Every new Antigravity working session must reconstruct the shared project context before substantial project work.

Required sequence:

```text
START SESSION
    ↓
LOCATE SHARED MEMORY
    ↓
LOAD CURRENT PROJECT CONTEXT
    ↓
UNDERSTAND USER PROMPT
    ↓
CHECK PREVIOUS WORK
    ↓
PLAN
    ↓
IMPLEMENT
    ↓
VERIFY
    ↓
UPDATE SHARED MEMORY
    ↓
END SESSION
```

Detailed startup:

### Step 1 — Locate Shared Folder

Find:

```text
history-conversation-agent
```

using Google Drive MCP.

### Step 2 — Read Current State

Read:

```text
CURRENT_STATE.md
```

This is mandatory.

### Step 3 — Read Working History

Read:

```text
WORKING_HISTORY.md
```

Determine:

- Current work.
- Unfinished work.
- Known problems.
- Previous decisions.
- Next actions.

### Step 4 — Read Project Tree

Read:

```text
PROJECT_TREE.md
```

Understand:

- Project structure.
- Architecture.
- Important components.
- Relevant directories.

### Step 5 — Read Recent Changes

Read recent entries from:

```text
CHANGE_HISTORY.md
```

Prioritize the latest changes.

Do not load the entire history if it is unnecessarily large.

### Step 6 — Inspect Recent Workdays

Inspect:

```text
workdays/
```

Identify the most recent relevant workday.

Read recent relevant sessions when they relate to the user's request.

---

# 29. Context Loading Priority

Do not blindly load every historical session.

Use:

```text
Priority 1
CURRENT_STATE.md

Priority 2
WORKING_HISTORY.md

Priority 3
PROJECT_TREE.md

Priority 4
Recent CHANGE_HISTORY.md

Priority 5
Recent relevant workday sessions

Priority 6
Older sessions only when necessary
```

This keeps context useful without unnecessarily consuming the agent's context window.

---

# 30. Prompt Continuation

After loading shared memory:

1. Read the new user prompt.
2. Determine whether it continues existing work.
3. Identify relevant previous sessions.
4. Check whether another member already attempted the task.
5. Check whether the requested work is already completed.
6. Continue from the latest valid state.

Do not ask the user to explain previous work if that information already exists in shared history.

---

# 31. Continuing Another Member's Work

Example:

```text
MEMBER_A
    ↓
Implemented authentication
    ↓
Session persistence remains broken
    ↓
Saved state to Google Drive
```

Then:

```text
MEMBER_B
    ↓
Opens Antigravity
    ↓
"Continue fixing authentication."
```

The agent must:

1. Read `CURRENT_STATE.md`.
2. Read `WORKING_HISTORY.md`.
3. Read `PROJECT_TREE.md`.
4. Read recent `CHANGE_HISTORY.md`.
5. Locate MEMBER_A's relevant session.
6. Understand what MEMBER_A implemented.
7. Continue from that state.

Do not restart an existing implementation from zero.

---

# 32. Source of Truth

There are two sources of truth.

## Source Code

The actual project repository is authoritative for:

- Current implementation.
- Actual files.
- Actual code.
- Actual configuration.
- Actual test results.

## Shared History

Google Drive history is authoritative for:

- Development context.
- Previous decisions.
- Project intent.
- Unfinished work.
- Team working history.
- Previous prompts.
- Previous agent sessions.

If shared history conflicts with source code:

1. Inspect the code.
2. Determine the actual current state.
3. Treat source code as authoritative for implementation state.
4. Update shared history to match the verified state.

Never blindly trust stale history.

---

# 33. Before Coding

Before modifying code:

1. Understand current project state.
2. Identify relevant files.
3. Read recent changes.
4. Check previous attempts.
5. Check known problems.
6. Check architectural decisions.
7. Check whether another developer is working on the same area.
8. Avoid duplicating existing work.
9. Define the smallest implementation required by the user's request.

---

# 34. During Coding

Work normally in the project repository.

Focus on the requested task.

Do not unnecessarily modify shared history during intermediate investigation.

Do not use shared history as a substitute for actual source code.

Do not expand scope.

Do not perform unrelated cleanup.

---

# 35. After Coding

After meaningful work:

1. Verify the implementation.
2. Update the current session.
3. Update `CHANGE_HISTORY.md`.
4. Update `PROMPT_HISTORY.md`.
5. Update `WORKING_HISTORY.md`.
6. Update `CURRENT_STATE.md`.
7. Update `PROJECT_TREE.md` if project structure or architecture changed.

Required order:

```text
1. Verify work
       ↓
2. Update SESSION_NNN.md
       ↓
3. Update CHANGE_HISTORY.md
       ↓
4. Update PROMPT_HISTORY.md
       ↓
5. Update WORKING_HISTORY.md
       ↓
6. Update CURRENT_STATE.md
       ↓
7. Update PROJECT_TREE.md if necessary
```

---

# 36. Concurrency

Multiple team members may work simultaneously.

Before updating shared state:

1. Re-read `CURRENT_STATE.md`.
2. Read recent `CHANGE_HISTORY.md`.
3. Check recent workday sessions.
4. Merge the latest verified information.
5. Avoid overwriting another member's newer work.

If two members made conflicting changes:

```text
Do not delete either history.

Record both changes.

Identify the conflict.

Mark the current state as requiring reconciliation.
```

Never silently overwrite another developer's newer history.

---

# 37. History vs Current Memory

The distinction is:

```text
Root files
    =
Current shared memory

workdays/
    =
Historical memory
```

Do not use daily session files as a replacement for `CURRENT_STATE.md`.

Do not allow `CURRENT_STATE.md` to become a full conversation transcript.

`CURRENT_STATE.md` must remain concise and actionable.

Daily sessions contain detailed historical information.

---

# 38. Tree Graph

The project tree can represent:

1. File and directory structure.
2. Logical architecture.

For a Laravel project, the logical architecture should normally reflect:

```text
User
  ↓
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
```

Update the tree graph when architecture changes.

Do not regenerate it for every minor code modification.

---

# 39. Important Decisions

Architectural decisions must be preserved.

Example:

```markdown
## Decision

Business logic must remain in the Service layer.

## Reason

Controllers must remain thin and the project uses
Service Interfaces as the business contract.

## Date

YYYY-MM-DD

## Decided By

MEMBER
```

Do not silently reverse an existing architectural decision.

If a new requirement requires changing it:

1. Record the new decision.
2. Record why it changed.
3. Update the project tree if necessary.
4. Update current state.

---

# 40. Sensitive Information

Never store secrets in shared history.

Never write:

- API keys.
- Passwords.
- OAuth client secrets.
- Access tokens.
- Private keys.
- Session tokens.

If credentials are involved, record only:

```text
Credential configured successfully.
```

Never store the credential itself.

---

# 41. Google Drive Operations

Use Google Drive MCP for:

- Locating the shared folder.
- Reading shared history.
- Creating workday folders.
- Creating member folders.
- Creating session files.
- Updating current memory.
- Reading previous sessions.

Do not use the local filesystem as a replacement for shared Google Drive history.

Local project files and shared history have different purposes.

---

# 42. Google Drive Failure Handling

If Google Drive is temporarily unavailable:

1. Continue project work if enough local context exists.
2. Do not fabricate that history was saved.
3. Clearly mark the history update as pending.
4. Synchronize when Drive becomes available.

Never claim:

```text
History saved successfully.
```

unless the Google Drive operation actually succeeded.

---

# 43. History Operations and User Communication

History operations should normally be performed silently.

Do not repeatedly tell the user that history was read or written.

Mention shared history only when it is relevant.

Example:

```text
I found the previous authentication work. The service is already implemented, and the remaining issue is session persistence. I will continue from there.
```

Do not expose Google Drive MCP implementation details unless the user asks.

---

# 44. Session Completion

At the end of a meaningful task, the session record must contain:

```text
Member
Date
Prompt
Intent
Work Performed
Files Added
Files Modified
Files Deleted
Architecture Changes
Verification
Result
Remaining Work
Next Recommended Action
```

---

# 45. Final Task Completion Rules

A task is considered complete only when:

1. The requested requirement has been implemented.
2. No unrelated feature was added.
3. The implementation follows the project's MVC architecture.
4. The correct Service Interface is used where applicable.
5. Business logic is in the Service.
6. Authorization and validation are handled in their appropriate layers.
7. Other developers' work has not been unnecessarily changed.
8. Relevant errors have been fixed when possible.
9. The result has been verified when possible.
10. Shared history has been updated.
11. Remaining work is explicitly recorded when the task is not fully complete.

If any of these cannot be satisfied, record the reason.

---

# 46. Continuity Objective

The final objective is:

```text
Member A
    ↓
Works on project
    ↓
Agent records work
    ↓
Google Drive
    ↓
Shared history
    ↓
Member B
    ↓
Opens Antigravity
    ↓
Starts prompt
    ↓
Agent reconstructs context
    ↓
Understands Member A's work
    ↓
Continues project
```

A new team member should be able to continue the project without requiring another team member to manually explain previous development history.

The shared history must make the project understandable to another agent.

---

# 47. Final Shared Memory Model

```text
history-conversation-agent/
│
├── CURRENT_STATE.md
│       │
│       └── What is happening now?
│
├── PROJECT_TREE.md
│       │
│       └── How is the project structured?
│
├── WORKING_HISTORY.md
│       │
│       └── Why are we here?
│
├── PROMPT_HISTORY.md
│       │
│       └── What did the team ask the agent?
│
├── CHANGE_HISTORY.md
│       │
│       └── What changed?
│
└── workdays/
        │
        ├── 2026-10-05/
        │   ├── MEMBER_A/
        │   │   ├── SESSION_001.md
        │   │   └── SESSION_002.md
        │   └── MEMBER_B/
        │       └── SESSION_001.md
        │
        ├── 2026-10-06/
        │   └── ...
        │
        └── ...
```

This structure provides persistent shared working memory across team members and Antigravity sessions.

The agent's primary responsibility is continuity:

```text
Understand what previous members did.
        ↓
Preserve what they learned.
        ↓
Protect their work.
        ↓
Continue from the latest verified state.
        ↓
Record the new verified state.
```

# 48. Non-Negotiable Rules

The following rules override convenience:

1. Do exactly the user's requested task.
2. Do not add unrequested functionality.
3. Use standard Laravel MVC.
4. Use the approved Service Interfaces.
5. Put business logic in Services.
6. Keep Controllers thin.
7. Use Policies for authorization.
8. Use Form Requests for request validation.
9. Use Models for Eloquent behavior and persistence-related behavior.
10. Keep code simple, explicit, hard-coded, and readable.
11. Do not abbreviate identifiers.
12. Write code comments in Vietnamese.
13. Protect other developers' work.
14. Stop before an unavoidable cross-developer conflict and report it.
15. Treat user-supplied images as implementation requirements.
16. Automatically fix in-scope errors when possible.
17. Never claim verification that did not happen.
18. Load shared history before substantial work.
19. Treat source code as authoritative for actual implementation state.
20. Update shared history after meaningful work.
21. Never store secrets in shared history.
22. Never silently overwrite newer team history.
23. Never invent business rules, files, routes, database fields, tests, or results.
24. Do not over-engineer.
25. Do not be creative when the user has specified a concrete implementation.
