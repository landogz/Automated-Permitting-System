# Process Flows

## Account registration (before applying)

```mermaid
flowchart LR
  Register[Self register] --> Pending[Pending]
  Pending --> Admin[Admin approve or decline]
  Admin -->|Approved| Login[Login]
  Admin -->|Declined| Stop[Cannot login]
  Login --> Apply[Online Application]
```

Emails: registration received (applicant + admin alert), approved, declined (with reason).

## End-to-end permitting (Phase I)

```mermaid
flowchart LR
  Apply[Online Application] --> Intake[Intake Completeness]
  Intake --> Classify[Permit Classifier]
  Classify --> Eval[Evaluation and Routing]
  Eval --> Inspect[Inspection]
  Inspect --> Pay[Order of Payment]
  Pay --> Compliance{Compliant?}
  Compliance -->|Yes| Release[Records Release]
  Compliance -->|No| Notice[G-03 or G-04]
  Notice --> Eval
  Release --> Archive[Archiving Logs]
```

## Status timeline
Applicant account: Pending → Approved / Declined.  
Application: Draft → Submitted → Under Evaluation → For Inspection → For Payment → For Release → Released / Disapproved.
