# SKILL.md

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
