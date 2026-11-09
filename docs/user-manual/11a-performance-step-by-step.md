# SECTION 11A: PERFORMANCE MANAGEMENT, STEP BY STEP

**Audience:** HR officers, management, department heads, supervisors and employees
**Companion to:** [Section 11: Performance Management](./11-performance-management.md), which holds the full rules, score formulas and known gaps

This guide walks through one appraisal cycle in order, from setting up the organisational plan to monitoring progress on the Performance Dashboard. One example is carried through every step so you can see how the numbers flow from the institution down to a single employee and back up again.

---

## Table of Contents

1. [The cycle at a glance](#the-cycle-at-a-glance)
2. [Before you start](#before-you-start)
3. [The example used in this guide](#the-example-used-in-this-guide)
4. [Step 1: Create the organisational plan](#step-1-create-the-organisational-plan)
5. [Step 2: Cascade to department plans](#step-2-cascade-to-department-plans)
6. [Step 3: Cascade to employee plans](#step-3-cascade-to-employee-plans)
7. [Step 4: Record implementation](#step-4-record-implementation)
8. [Step 5: Verify implementation](#step-5-verify-implementation)
9. [Step 6: Monitor progress on the Performance Dashboard](#step-6-monitor-progress-on-the-performance-dashboard)
10. [Step 7: Evaluate and approve](#step-7-evaluate-and-approve)
11. [Routine for each role](#routine-for-each-role)
12. [Dashboard questions](#dashboard-questions)

---

## The cycle at a glance

| Step | What happens | Who | Screen |
|---|---|---|---|
| 1 | Set the institution's goals for the period | Top management / planning unit | Staff Performance → Organizational Plans |
| 2 | Give each department its share of those goals | Department head | Staff Performance → Department Plans |
| 3 | Give each employee their share of the department's goals | Supervisor / HR, or the employee | Staff Performance → Employee Plans, or My Performance → My Planning |
| 4 | Record work done, with quantity and evidence | Employee | My Performance → My Implementation |
| 5 | Check the work and accept or reject it | Supervisor / verifier | My Performance → Verify Implementations |
| 6 | Track progress from the organisation down to each person | HR officers and management | Staff Performance → Performance Dashboard |
| 7 | Self-evaluation, review and approval | Employee, then approvers | My Performance → My Evaluation / Approve Evaluations |

Steps 1 to 3 happen once at the start of the period. Steps 4 to 6 repeat every month. Step 7 happens at the end.

```text
 PLAN (start of period)           DO (every month)                REVIEW (end of period)
 ----------------------           ----------------                ----------------------
 1 Organisational plan  ───┐      4 Employee records work  ──┐    7 Self-evaluation
 2 Department plans     ───┤      5 Supervisor verifies    ──┤      Supervisor review
 3 Employee plans       ───┘      6 HR monitors dashboard  ──┘      Final score and rating
```

---

## Before you start

An administrator must finish this setup **once**, before the first plan is created. The details are in [Section 11: Before you start](./11-performance-management.md#before-you-start-system-setup).

- [ ] **Permissions granted** to the people in each role:

  | Permission | Lets the user |
  |---|---|
  | `manage-org-plans` | Create and approve organisational plans |
  | `manage-dept-plans` | Create department plans for their department |
  | `view-all-dept-plans` | See every department's plans, and the organisation level of the dashboard |
  | `manage-employee-plans` | Assign and activate employee plans, and verify entries |
  | `view-performance` | Open the Performance Dashboard (own department only, unless they also hold `view-all-dept-plans`) |

- [ ] **Every user linked to an employee record** with a department. Unlinked users can't use My Performance and see no department's plans.
- [ ] **Approval levels configured** for the `Performance` document type, with an approver covering every department. Without this, employees can't submit evaluations at Step 7.

> **Tip for HR officers:** give yourself `view-performance` and `view-all-dept-plans`. That is enough to monitor every department on the dashboard without being able to change anyone's plan.

---

## The example used in this guide

A hospital runs a one-year plan, **Strategic Plan 2026** (1 January to 31 December 2026). One of its goals is:

> **Patient visits:** increase total patient visits to 24,000 by 31 December.

It is delivered by two departments, and within OPD by individual clerks:

```text
Organisation   Patient visits                 24,000 visits   weight 40%
   ├─ OPD        Patient visits (OPD share)    14,000 visits
   │    ├─ Amina   Register OPD patient visits  1,200 visits
   │    └─ ...     (other OPD staff)           12,800 visits
   └─ Dental     Patient visits (Dental share) 10,000 visits
```

---

## Step 1: Create the organisational plan

**Who:** top management or the planning unit (`manage-org-plans`)
**Where:** Staff Performance → Organizational Plans

1. Click **CREATE NEW PLAN**.
2. Fill in the plan details:

   | Field | Example |
   |---|---|
   | Plan name | Strategic Plan 2026 |
   | Description | Institution goals for the 2026 financial year |
   | Start date / End date | 01/01/2026 to 31/12/2026 |
   | Period type | Yearly |
   | Status | **Draft** (leave it) |

3. Save. The plan appears in the list as Draft.
4. Click **Manage Items** on the plan and add each institution goal:

   | Field | Example | Why it matters |
   |---|---|---|
   | Item name | Patient visits | Short, starts with what is counted |
   | Description | Increase total patient visits from 20,000 to 24,000 | Makes the goal **Specific** |
   | Weight | 40 | How much this goal counts. All weights must total 100 |
   | Target value | 24000 | Makes it **Measurable**. Never leave it empty |
   | Target unit | visits | Shown on every level of the dashboard |
   | Min acceptable / Max possible | 22000 / 26000 | Used by the **Achievable** check |

5. Repeat until the **weights total exactly 100%**.
6. Click **Approve & Activate**. The plan becomes Active and records who approved it.

**Check before moving on:**
- [ ] Every goal has a description, a target value and a unit.
- [ ] Weights total 100%.
- [ ] The status was set with **Approve & Activate**, not by editing the status field. Editing skips the weight check.

> **Use counted units where you can** (visits, reports, sessions). For counted targets the dashboard adds up what the departments achieve and compares it with this target. For percentage targets (unit `%`) it can only average the departments' progress. See [Writing goals so the score comes out right](./11-performance-management.md#writing-goals-so-the-score-comes-out-right).

---

## Step 2: Cascade to department plans

**Who:** each department head (`manage-dept-plans`)
**Where:** Staff Performance → Department Plans

1. Click **ASSIGN PLAN TO DEPARTMENT**. Your department is pre-selected.
2. Choose the organisational plan (**Strategic Plan 2026**), enter a plan name such as *OPD Plan 2026*, and leave the status as **Draft**.
3. Save, then click **Distribute Items from Org Plan** (download icon). This copies every organisation goal into your plan, already linked to its organisation goal.

   > Distribute **first**. It only works while your plan has no items. Add department-only goals afterwards.

4. Click **Manage Items** and **edit each copied goal to your department's real share**. Copied goals arrive with the institution-wide target.

   | Goal | Copied target | Change it to |
   |---|---|---|
   | Patient visits | 24,000 | **14,000** (OPD's share) |

5. Delete goals your department doesn't contribute to, and re-balance the weights.
6. Add any department-only goals with **ADD ITEM**. These show on the dashboard as *Department-only goal*, because they don't serve an organisation goal.
7. Check that **Total Weight** reads 100%. The system warns but doesn't stop you.
8. Edit the plan and set its status to **Active**. Employees can only be given goals from active department plans.

**Check before moving on:**
- [ ] The department shares of each organisation goal add up to the organisation target (OPD 14,000 + Dental 10,000 = 24,000). On the dashboard, the **Assigned** column shows this for each organisation goal.
- [ ] Weights total 100%.
- [ ] The plan is Active.

---

## Step 3: Cascade to employee plans

Agree as an institution which option you use. Option A is recommended, because the supervisor controls the targets.

### Option A: Supervisor assigns the plan (recommended)

**Who:** supervisor or HR (`manage-employee-plans`)
**Where:** Staff Performance → Employee Plans

1. Click **ASSIGN PLAN TO EMPLOYEE**.
2. Pick the **Department**, then the **Department Plan** (*OPD Plan 2026*), then the **Employee** (*Amina*).
3. Enter a plan name such as *Amina – 2026*, and save it as **Draft**.
4. Click **Distribute Items from Dept Plan** to copy the department goals, or add goals one at a time under **Manage Items → ADD ITEM**.
5. **Edit each goal to this person's own target and weight:**

   | Goal | Copied target | Amina's target | Weight |
   |---|---|---|---|
   | Patient visits | 14,000 | **1,200** | 60 |
   | Client feedback reviews | 12 | 12 | 40 |

6. Discuss the goals with the employee, and record the agreement in the plan description (the system has no sign-off step).
7. Click **Activate Plan**.

### Option B: Employee drafts the plan

**Who:** the employee
**Where:** My Performance → My Planning

1. Select the department plan and click **CREATE NEW PLAN**.
2. Click **Add Goal**, link it to a department goal (the fields fill in), and change the target to your own share.
3. **Agree the goals with your supervisor first.**
4. Click **Submit for Review**. Despite the name, this makes the plan **Active immediately**, so step 3 must already be done.

**Check before moving on:**
- [ ] Every active member of staff has a plan. The dashboard shows **Staff with plans** compared with headcount, for each department.
- [ ] Every employee goal is linked to a department goal (the **R** check).
- [ ] The employees' targets for each department goal add up to the department target. On the dashboard, the **Assigned** column for each department goal shows this. Below 100% means part of the target belongs to nobody.
- [ ] Each plan's weights total 100%.

---

## Step 4: Record implementation

**Who:** the employee, throughout the period
**Where:** My Performance → My Implementation

1. Click **Record Implementation**.
2. Fill in the form:

   | Field | Example | Notes |
   |---|---|---|
   | Goal * | Patient visits | The goal this work counts toward |
   | Implementation Date * | 31/01/2026 | Can't be in the future |
   | Activity Title * | January OPD registrations | |
   | Activity Description | Registered walk-in and referral patients | |
   | **Quantity Achieved** | **100** | **The number that counts toward your target once verified. Leave it empty and the entry adds nothing** |
   | Unit | visits | Fills in from the goal |
   | Evidence/Supporting Documents | OPD register, January, pages 1–14 | Attach one file if you can: PDF, image, Word or Excel, up to 10 MB |
   | Challenges Faced / Lessons Learned | System downtime on 12 January | Useful for the review conversation |

3. Save. The entry shows as **Pending** until your supervisor checks it.

**Rules to remember:**
- **Enter only the new work** for each entry. Quantities add up: enter `100` for January and `110` for February, never a running total of `210`.
- Record at least **once a month**. The dashboard flags a plan as **Inactive** after 30 days with nothing logged.
- Pending and rejected entries don't count. If an entry is rejected, read the reason, correct it and save. It goes back to Pending.

---

## Step 5: Verify implementation

**Who:** approvers covering the employee's department, and `manage-employee-plans` holders
**Where:** My Performance → Verify Implementations

1. The page opens on **Pending** entries. Search by employee name or number if needed.
2. Check the quantity against the evidence text and any attached file.
3. Click **Verify**. Optionally add:
   - **Task Score (1 to 10):** how well the work was done. It feeds the employee's task score, and the dashboard shows the average as **Quality**.
   - **Comment:** feedback the employee will see.
4. Or click **Reject** with a clear reason, such as *"Register shows 85, not 100. Please correct the quantity."*

Once verified, Amina's 100 visits count toward:

```text
Amina      100 / 1,200   =  8.3%  of her goal
OPD        sum of all OPD staff verified visits / 14,000
Hospital   OPD + Dental verified visits / 24,000
```

> **Verify every month.** The dashboard only counts verified work, so a backlog of pending entries makes everyone look behind. The dashboard shows each department's pending count, so it's easy to see where verification is falling behind.

---

## Step 6: Monitor progress on the Performance Dashboard

**Who:** HR officers and management (`view-performance`); department heads see their own department
**Where:** Staff Performance → **Performance Dashboard**

### 6.1 Open the dashboard and pick a plan

1. Open **Staff Performance → Performance Dashboard**.
2. The current active plan opens automatically. To look at another one, choose it in **Organisational plan** at the top right.
3. Read the **Time elapsed** bar first. Every status on the page is measured against it.

   On 2 July 2026, about **50%** of Strategic Plan 2026 has passed, so a goal "should" be about half done.

### 6.2 How to read a progress bar

Every goal, department and employee has the same bar:

```text
 ████████████░░░░░░│░░░░░░░░░░   45%
 └─ achieved ─┘     └ tick = where it should be by today (50%)
```

| Status | Meaning | With 50% of the period gone |
|---|---|---|
| ✔ **Complete** | Target reached | 100% |
| ⬆ **On track** | Within 10 points of the tick, or ahead | 40% or more |
| ⚠ **At risk** | 10 to 25 points behind the tick | 25% to 39% |
| ❗ **Behind** | More than 25 points behind | Under 25% |
| ➖ **No target** | Nothing measurable: the goal has no target | — |

Hover over any bar to see the exact achieved and expected figures.

### 6.3 Organisation level: is the institution on course?

Shown to users with `view-all-dept-plans`. The page has four parts:

**1. Headline figures (top row)**

| Tile | Question it answers | Act when |
|---|---|---|
| Overall achievement | How far is the whole plan, weighted by goal? | Status is At risk or Behind |
| Goals assigned | How many organisation goals has a department taken on? | Fewer than all |
| Departments planned | How many departments have a plan? | Fewer than all |
| Staff with plans | How many active staff have a plan? | Below 100% |
| Verified activities | How much work has been checked? How much is waiting? | The waiting count keeps growing |
| Evaluations approved | How far is the appraisal itself, and what is the average score? | At the end of the period |

**2. Organisation goals table.** For each goal: target, achieved, progress, status, how many departments deliver it, and **Assigned**, the share of the target handed down to departments.

- **Depts: None** (red) means no department has taken this goal on. Nobody is working on it, and it counts as 0% in the overall figure.
- **Assigned below 100%** (red) means the department shares don't add up to the target. In the example, if Dental's share were left at 5,000, Assigned would read 79% (19,000 of 24,000), and the goal could never show as complete.

**3. Departments table.** One row per department, lowest progress first, with staff planned against headcount, verified and pending entries, evaluations done and average score. Departments with no plan are listed at the bottom in grey. **Select a department** to open it.

**4. Side panels**
- **Evaluation progress:** how many plans are not started, draft, in review, approved or returned, plus the spread of ratings.
- **Plan quality (SMART):** how many goals pass each SMART check.
- **Needs attention:** planning gaps found automatically, most serious first. Work through this list at every monthly review.

### 6.4 Department level: who is delivering, who needs help?

Open it by selecting a department (department heads land here directly).

- **Headline figures:** department achievement, staff with plans, verified entries, entries **awaiting verification**, **inactive plans** (nothing logged in 30 days) and evaluations approved.
- **Department goals:** each goal shows which organisation goal it **serves**, how many staff goals deliver it, and **Assigned**, the share of the target given to staff.
  - **Staff goals: None** means no employee goal delivers this department goal.
- **Staff plans:** one row per person, lowest progress first. Use the **search box** to find a person, or the **status filter** to list only *At risk* or *Behind* staff. Look out for:
  - an **Inactive** badge: nothing recorded in 30 days, so follow up with the employee
  - a high **Pending** count: follow up with the supervisor
- **Select a person** to open their plan.

### 6.5 Employee level: the evidence behind the numbers

- **Profile card:** plan achievement, status, and counts of verified, pending and rejected entries.
- **Evaluation card:** self, supervisor, task-quality and final scores, with the rating once approved.
- **Goals and measurable results:** for each goal, the department goal it serves, weight, target, achieved (verified only), progress, entries (verified / pending / rejected) and **Quality**, the average verifier score out of 10.
- **Recent implementation:** the latest entries, with links to attached evidence.

Use the breadcrumb at the top (**Organisation › OPD › Amina**) to step back up.

### 6.6 SMART chips

Each goal carries five letters. A crossed-out grey letter means that check failed:

| Chip | Passes when | To fix it |
|---|---|---|
| **S** Specific | The goal has a description | Add a description saying what exactly is done |
| **M** Measurable | It has a target value above zero **and** a unit | Set Target value and Target unit |
| **A** Achievable | The target lies between Min acceptable and Max possible | Correct the range or the target |
| **R** Relevant | It is linked to a goal one level up | Link the employee goal to a department goal, or the department goal to an organisation goal |
| **T** Time-bound | It belongs to a dated plan | Make sure the employee plan sits under a department plan |

### 6.7 Turning the dashboard into action

| You see | Likely cause | Who acts | What to do |
|---|---|---|---|
| Organisation goal with **Depts: None** | Not distributed, or deleted from department plans | Department heads | Add the goal to the right department plans (Step 2) |
| **Assigned** below 100% | Shares don't add up to the target | Department head / supervisor | Raise the department or staff targets so they add up (Steps 2–3) |
| Department **staff planned** below headcount | New or missed staff | Supervisor / HR | Assign the missing employee plans (Step 3) |
| High **Pending** count | Verification backlog | Supervisors | Clear Verify Implementations (Step 5) |
| **Inactive** employee | Work not being recorded | Employee and supervisor | Record the missing months (Step 4) |
| Goal **Behind** with recent activity | Real performance problem, or target too high | Supervisor and employee | Agree a recovery plan, and record the conversation |
| Many crossed-out **M** chips | Targets missing | Plan owners | Add target values and units |
| Weights warning in **Needs attention** | Plan weights don't total 100 | Plan owner | Re-balance the weights |

### 6.8 Sharing a view

The address bar keeps the plan, department and employee you have open. Copy the link to send a colleague straight to, for example, the OPD page. They still only see what their permissions allow.

---

## Step 7: Evaluate and approve

At the end of the period:

1. **Employee:** My Performance → My Evaluation → **Submit for Evaluation**. The self score is calculated from verified work.
2. **Approver:** My Performance → Approve Evaluations → **Review & Approve**. Clear any pending entries, meet the employee, enter the supervisor score and comments, then approve or reject.
3. After the last approval, the final score is calculated and the evaluation appears as **Approved** on the dashboard, with its rating.

The full procedure, the score formulas and the rating bands are in [Section 11: Steps 5 and 6](./11-performance-management.md#step-5-self-evaluation).

---

## Routine for each role

| When | Employee | Supervisor | Department head | HR officer / management |
|---|---|---|---|---|
| **Start of period** | Agree goals with supervisor | Assign and activate employee plans | Create, adjust and activate the department plan | Approve the organisational plan. Check on the dashboard that every goal is assigned and every department and employee has a plan |
| **Every month** | Record the month's work with evidence | Verify all pending entries | Review the department page: Behind and Inactive staff | Review **Needs attention**, the departments table and pending counts. Follow up gaps |
| **Every quarter** | Review own progress with supervisor | Hold a progress conversation with each At risk / Behind employee | Report department progress to management | Present the organisation level to management |
| **End of period** | Submit self-evaluation | Clear pending entries; review and approve | Make sure every evaluation is submitted | Track **Evaluations approved** to 100%; review the rating spread |

---

## Dashboard questions

**I can't see Performance Dashboard in the menu.**
You need `view-performance` (or a plan-management permission). Ask your administrator.

**I only see my own department.**
That is by design. The organisation level and other departments need `view-all-dept-plans`.

**The dashboard says "Your department has no plan under this plan yet."**
Your department head hasn't created a department plan for the selected organisational plan. Pick another plan, or complete Step 2.

**An employee finished their goal but the department isn't moving.**
Check the department goal's **Assigned** column. If staff targets only add up to part of the department target, the department can't reach 100% from them alone.

**An employee has recorded lots of work but shows 0%.**
Their entries are probably still **Pending**, or were recorded without a **Quantity Achieved**. Only verified quantities count. Open the employee page and check the entry counts.

**A goal shows "No target".**
It has no target value, so progress can't be measured and its **M** chip is crossed out. Department and employee plan progress leave it out; at the organisation level it counts as 0%. Add a target.

**Why does the overall figure seem low when most departments are on track?**
Organisation goals that no department has taken on count as 0%, weighted like any other goal. Check for **Depts: None** in the organisation goals table.

**How is each number calculated?**
Open **How the numbers are calculated** at the bottom of the dashboard, or see [How the score is calculated](./11-performance-management.md#how-the-score-is-calculated) for evaluation scores.
