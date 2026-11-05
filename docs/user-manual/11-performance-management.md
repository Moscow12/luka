# SECTION 11: PERFORMANCE MANAGEMENT (OPEN PERFORMANCE APPRAISAL)

**Target audience:** All staff, supervisors, department heads, HR, system administrators
**Describes the system as of:** November 2026

## Table of Contents
- [Overview](#overview)
- [How to read this guide](#how-to-read-this-guide)
- [Who does what](#who-does-what)
- [The SMART rule](#the-smart-rule)
- [Before you start: system setup](#before-you-start-system-setup)
- [Step 1: Organizational plan](#step-1-organizational-plan)
- [Step 2: Department plan](#step-2-department-plan)
- [Step 3: Employee plan](#step-3-employee-plan)
- [Step 4: Implementation](#step-4-implementation)
- [Step 5: Self-evaluation](#step-5-self-evaluation)
- [Step 6: Supervisor review and approval](#step-6-supervisor-review-and-approval)
- [How the score is calculated](#how-the-score-is-calculated)
- [Worked example](#worked-example)
- [Suggested appraisal calendar](#suggested-appraisal-calendar)
- [Known gaps and recommended changes](#known-gaps-and-recommended-changes)
- [Troubleshooting](#troubleshooting)

---

## Overview

Open performance appraisal means the employee can see their goals, their progress and how they were scored, and is part of the conversation about each of them.

In this system, goals cascade from the institution down to each employee. Work is then recorded against those goals, scored, and approved:

```text
ORGANIZATIONAL PLAN    What the institution wants to achieve this period
        |              Weights must total exactly 100% before approval
        v
DEPARTMENT PLAN        What each department contributes
        |              One plan per department per organizational plan
        v
EMPLOYEE PLAN          What each person will deliver
        |
        v
IMPLEMENTATION         What was actually done, recorded with quantity and evidence
        |
        v
VERIFICATION           A supervisor checks the evidence, and can score the task and comment.
        |              Only verified work counts
        |
        v
SELF-EVALUATION        Employee submits; the system calculates the self score
        |
        v
SUPERVISOR REVIEW      Each approval level scores and comments, or rejects.
        |              Goes to approvers covering the employee's department
        |
        v
FINAL SCORE            Average of the self, supervisor and task scores
```

---

## How to read this guide

The system does not yet enforce everything a sound appraisal needs. This guide marks the difference:

| Marker | Meaning |
|---|---|
| **In the system** | The system does this, or enforces it |
| **SMART rule** | The system does not check this. Your institution must apply it as a rule when plans are reviewed |
| **Gap G*n*** | Something missing or unreliable, listed under [Known gaps](#known-gaps-and-recommended-changes) |

> **Read the open gaps before running a live appraisal cycle**, and complete the [system setup](#before-you-start-system-setup) first. Without it, evaluations can't be submitted.

---

## Who does what

| Role | Responsibility | Where | Access needed |
|---|---|---|---|
| Top management / planning | Create and approve the organizational plan | Staff Performance → Organizational Plans | `manage-org-plans` |
| Department head | Create the department plan and its items | Staff Performance → Department Plans | `manage-dept-plans` |
| Supervisor / HR | Assign employee plans, set goals, review | Staff Performance → Employee Plans | `manage-employee-plans` |
| Employee | Plan goals, record work, submit for evaluation | My Performance | A linked employee record |
| Verifier | Check recorded work against its evidence | My Performance → Verify Implementations | An approver covering the employee's department, or `manage-employee-plans` for plans they can see |
| Approver | Score and approve evaluations | My Performance → Approve Evaluations | Assigned to an approval level mapped to `Performance`, covering the employee's department |
| Administrator | Configure approval levels and permissions | `/setup/setup/approvalconfigurations` | Administrator access |

**Who can see which department:**

- Department Plans and Employee Plans show the user's own department only, unless they hold `view-all-dept-plans` or are a super admin.
- Staff without the matching `manage-*` permission can view plans but not change them.
- Nobody can verify their own entries or approve their own evaluation, whatever their permissions.

---

## The SMART rule

**Every item at every level, including organizational, department and employee goals, must be SMART.** A goal that fails any test below should be rewritten before its plan is approved or activated.

| Letter | Test | Fields to use | Enforced by the system? |
|---|---|---|---|
| **S**pecific | Does it say exactly what will be done, and for whom or what? | Item Name, Description | Name required; clarity is not checked |
| **M**easurable | Is there a number to count, with a unit? | Target Value, Target Unit | **No. Target is optional** (G6) |
| **A**chievable | Is it realistic given staff, budget and past results? | Target Value, Min Acceptable, Max Possible | No |
| **R**elevant | Does it support a higher-level goal? | Department Plan Item link | Optional on employee goals |
| **T**ime-bound | Is there a deadline? | Plan start and end dates (organizational level only). State anything shorter in the Description | **No. Items have no due date** (G5) |

### SMART checklist for reviewers

Before approving or activating a plan, confirm that every item:

- [ ] Starts with an action and names what it applies to
- [ ] Has a **Target Value** greater than zero and a **Target Unit**
- [ ] Has a target that is realistic compared with last period's result
- [ ] Links to a department plan item, or has an agreed reason not to
- [ ] States its deadline in the Description if it ends before the plan does
- [ ] Has a named evidence source, such as a register, report or system export

The plan as a whole must also have **weights totalling exactly 100%**, with the largest weights on the most important goals.

### Weak and SMART goals

| Level | Not SMART | SMART |
|---|---|---|
| Organizational | Improve hospital revenue | Increase total patient attendance from 20,000 to 24,000 visits by 30 June |
| Department | Get more NHIF patients | ONGEZEKO LA WAGONJWA, NHIF: raise NHIF patient visits from 10,000 to 12,000 by 30 June |
| Employee | Work hard at registration | Register 1,200 NHIF patient visits with verified membership by 30 June, reported monthly |
| Employee (quality) | Improve customer care | Complete 12 monthly client feedback reviews, with actions recorded, by 30 June |

### Writing goals so the score comes out right

The score formula only works well when more is better and the numbers add up. Until gaps G7 and G8 are closed, write goals in this form:

| Kind of goal | Avoid | Write it as | Target |
|---|---|---|---|
| Lower is better | Reduce waiting time to 30 minutes | Months in which average waiting time was under 30 minutes | 12 months |
| Percentage | Achieve 90% client satisfaction | Quarterly surveys reaching at least 90% satisfaction | 4 surveys |
| Yes/no or one-off | Complete the staff handbook | Staff handbook approved by 31 March | 1 document |
| Quality or behaviour | Be a good team player | Peer feedback rounds completed with an agreed action plan | 2 rounds |

**Always set a target.** A goal with no target always scores zero, yet its weight still counts against the employee (G6).

**Same name, different goals:** one plan may contain two items with the same name, such as ONGEZEKO LA WAGONJWA for NHIF and for cash. Put `NHIF` or `CASH` at the start of each Description. It appears beside the name when employees pick items.

---

## Before you start: system setup

An administrator must complete this once, **before anyone submits an evaluation**. Until an approver exists for an employee's department, the system refuses that employee's submission and asks them to contact the administrator.

1. **Grant permissions** using the table under [Who does what](#who-does-what).
2. **Link users to employees.** Without a linked employee record and department, a user cannot use My Performance and sees no department's plans.
3. **Configure approvals** at `/setup/setup/approvalconfigurations`:
   1. Create the approval levels, such as Level 1 Supervisor and Level 2 Head of Department. The lowest order is where evaluations start, and it doesn't need to be `1`.
   2. Map each level to the document type **Performance**. Use `Performance`, not `Appraisal`, which the evaluation flow does not read.
   3. Assign the approving employees to each level, and choose the departments each one covers. **Leaving departments empty makes that approver company-wide.**
   4. Give every department an approver at each level it should pass through. **Approvers need a backup too:** an approver's own evaluation goes to someone else at that level, or skips to the next level. If nobody else can act on it, they can't submit.
4. **Test it.** Submit one test evaluation, and confirm that the right approver can see it under **Approve Evaluations**.

**How routing works:**

- An evaluation only passes through levels that have an eligible approver for that employee, meaning an active employee with a login, other than the employee, covering their department. Levels with nobody eligible are skipped.
- If the setup changes, or an evaluation was submitted before approvals were configured, it moves to a valid level the next time an approver opens **Approve Evaluations**.

---

## Step 1: Organizational plan

**Who:** top management or the planning unit
**Where:** Staff Performance → Organizational Plans

1. Click **CREATE NEW PLAN**. Enter the name, description, start date, end date and period type (yearly, quarterly or monthly).
2. Leave the status as **Draft**.
3. Use **Manage Items** to add the institution's goals. Each needs a name, weight and, per the SMART rule, a target and unit.
4. Check that weights total exactly 100%.
5. Click **Approve & Activate**.

**In the system:**
- A plan can't be approved without items, or unless weights total exactly 100%.
- Approving records who approved it and when, and sets the status to Active.
- The end date must be after the start date.

**SMART rule:** always activate through **Approve & Activate**. The status field also offers Active, which skips the weight check and the approval record (G9).

---

## Step 2: Department plan

**Who:** department head, or planning staff with all-department access
**Where:** Staff Performance → Department Plans

1. Click **ASSIGN PLAN TO DEPARTMENT**. Your own department is pre-selected, and only `view-all-dept-plans` holders can pick another.
2. Choose the organizational plan, enter a plan name and description, and leave the status as **Draft**.
3. Add items in one of two ways:
   - **Distribute Items from Org Plan** (download icon) copies all organizational items. It only works while the plan has no items, so **distribute first and then add your own**.
   - **Manage Items → ADD ITEM** adds an item directly. Choose the department plan, then enter the name, description, weight and target.
4. Adjust distributed targets to this department's real share. They arrive with the institution-wide values.
5. Check that the **Total Weight** on the items page reads 100%. The system warns but doesn't stop you (G9).
6. Edit the plan and set its status to **Active**. Employees can only pick active plans.

**In the system:**
- Each department can have one plan per organizational plan.
- Active and completed plans can't be deleted, and neither can plans that have employee plans.
- An item already distributed to employees can't be deleted.

---

## Step 3: Employee plan

There are two ways to create a plan. Agree which one your institution uses.

### Option A: Supervisor assigns the plan

**Where:** Staff Performance → Employee Plans

1. Click **ASSIGN PLAN TO EMPLOYEE**.
2. **Department:** your own is pre-selected. This limits the next two lists.
3. **Department Plan:** only that department's active plans are listed.
4. **Employee:** only that department's active staff are listed.
5. Enter a plan name, and save it as **Draft**.
6. Add goals with **Distribute Items from Dept Plan**, or under **Manage Items → ADD ITEM**. Set each person's own target and weight.
7. Discuss the goals with the employee. **The system has no sign-off step, so record the agreement in the plan's description** (G10).
8. Click **Activate Plan**. This requires at least one item.

When picking items, those already in this plan appear greyed out as "already added". To give the employee more, add items to the department plan first.

### Option B: Employee drafts the plan

**Where:** My Performance → My Planning

1. Select a department plan to see its goals.
2. Create a plan based on that department plan.
3. Click **Add Goal**. Link it to a department goal to auto-fill the fields, then adjust the target.
4. Agree the goals with your supervisor **before** continuing.
5. Click **Submit for Review**.

> **Note:** despite its name, Submit for Review makes the plan **Active immediately**, with no supervisor check (G10). That is why step 4 must happen first.

**SMART rule:** the supervisor applies the [SMART checklist](#smart-checklist-for-reviewers) and confirms weights total 100% before activation. Only the organizational level enforces this (G9).

---

## Step 4: Implementation

**Who:** employee, throughout the period
**Where:** My Performance → My Implementation

Record each piece of work as it happens, against an active or completed plan:

| Field | What to enter |
|---|---|
| Goal * | The plan item this work counts toward |
| Implementation Date * | When it was done. Can't be in the future |
| Activity Title * | Short description |
| **Quantity Achieved** | **The number that counts toward the target, once verified. Leave it empty and this entry adds nothing to your score** |
| Unit | Fills in from the goal |
| Evidence/Supporting Documents | Where the proof can be found, such as a register or report reference. You can also attach one file: PDF, image, Word or Excel, up to 10 MB. Files are private to you and those who can verify the entry |
| Challenges Faced / Lessons Learned | Useful material for the review conversation |

**Good practice:**
- Record at least monthly, so the numbers are never rebuilt from memory.
- Enter only this period's new work, because quantities **add up** across entries. Enter `100` this month and `120` next month, not a running total of `220`.

**Entry status:**

| Status | Meaning | Can the employee change it? |
|---|---|---|
| Pending | Waiting for verification. **Doesn't count yet** | Yes |
| Verified | Checked. **Counts toward the score**, and may carry a task score and comment | No, it's locked |
| Rejected | Not accepted, with the reason shown. Doesn't count | Yes. Saving a correction sends it back as Pending |

### Verifying entries

**Who:** approvers covering the employee's department, and `manage-employee-plans` holders who can see the plan
**Where:** My Performance → Verify Implementations

1. The page opens on **Pending** entries. Search by activity, employee name or number if needed.
2. Check the quantity against the evidence text and any attached file.
3. Click **Verify**. You can add:
   - **Task Score (1 to 10):** how well the task was done. Optional. It adds to the employee's [final score](#how-the-score-is-calculated).
   - **Comment:** feedback for the employee. Optional.
4. Or click **Reject**, with a reason telling the employee what to correct. Rejected entries carry no score.

The employee sees the score and comment on My Implementation. Score consistently: if colleagues in a department are scored on different standards, their final scores won't be comparable.

Verify regularly, ideally at each monthly or quarterly check-in, so employees see accurate progress. You can change a decision or score (**Edit score**) until the evaluation is approved; after that, its entries are locked.

Progress is visible at any time under **My Performance → Overview**, and reflects verified work only.

---

## Step 5: Self-evaluation

**Who:** employee, at the end of the period
**Where:** My Performance → My Evaluation

1. Confirm your implementation entries are complete.
2. Find the plan under the plans ready for evaluation, and click **Submit for Evaluation**.
3. Add your comments: what went well, what got in the way, and support you need.
4. Submit.

**In the system:**
- **You don't type the self score.** It's calculated from your **verified** entries. See [How the score is calculated](#how-the-score-is-calculated).
- You can submit while entries are still pending, and the form tells you how many. They don't count yet, and your score updates when your approver acts.
- A plan must have at least one goal.
- If no approver is set up for your department, submission is refused with a message to contact your administrator.
- If an evaluation is rejected, the plan becomes available again. Correct your entries and resubmit.

**SMART rule:** only submit after the plan's end date. The system allows submission at any time (G5).

---

## Step 6: Supervisor review and approval

**Who:** employees assigned to a `Performance` approval level, for the departments they cover
**Where:** My Performance → Approve Evaluations

You only see evaluations at your level, from departments you cover. Your own never appears here.

1. Open the evaluation with **Review & Approve**.
2. Compare the goals, the implementation entries and the evidence, then **meet with the employee**. An open appraisal should never be scored without that conversation.
3. **Clear any pending entries.** If entries are still pending, the form shows how many with a link to verify them. You can't approve until none remain, though you can reject.
4. Enter the **Supervisor Score** (0 to 100). It starts at the employee's current self score, so change it deliberately.
5. Record **Strengths**, **Areas for Improvement**, **Recommendations** and **Supervisor Comments**.
6. Choose **Approve** or **Reject**. A rejection needs a reason of at least 10 characters.

**What happens next:**

| Decision | Result |
|---|---|
| Approve, more levels remain | Self score is updated, status becomes **Under Review** and it moves to the next level with an eligible approver |
| Approve at the last level | Self score is updated, status becomes **Approved**, the final score is calculated, and the entries lock |
| Reject at any level | Status becomes **Rejected** and the employee can correct and resubmit |

---

## How the score is calculated

### Self score (calculated by the system)

For each goal:

```text
Achieved       = sum of Quantity Achieved in verified entries only
Goal %         = Achieved / Target x 100, capped at 100
Goal points    = Goal % x Weight / 100
```

For the plan:

```text
Self score     = (sum of Goal points / sum of all Weights) x 100
```

The self score is calculated at submission, then recalculated at each approval, so it always matches the work verified by then.

### Task score (from verifiers' scores)

Calculated from the 1 to 10 scores given to verified entries, out of 100:

```text
Goal task %    = average of that goal's entry scores x 10
Task score     = sum of (Goal task % x Weight) / sum of the Weights of scored goals
```

Goals with no scored entries are left out, not counted as zero. If no entry has been scored, there is no task score. It's also recalculated at each approval.

### Supervisor score (entered by the approver)

A number from 0 to 100, based on judgement. Each approval level overwrites the previous one, so **only the last level's score counts** toward the final score.

### Final score

```text
With a task score:     Final score = (Self score + Supervisor score + Task score) / 3
Without a task score:  Final score = (Self score + Supervisor score) / 2
```

The Overview's average score uses these final scores, so task scores feed into the employee's general performance once an evaluation is approved.

### Things that affect the result

| Situation | Effect |
|---|---|
| Achieved more than the target | Capped at 100% for that goal, so over-achievement earns nothing extra and can't offset a shortfall elsewhere |
| Goal has no target | Scores 0, but its weight still counts, which lowers the total |
| Entry has no quantity | Adds nothing |
| Entry is pending or rejected | Doesn't count. Only verified entries do |
| Goal marked inactive | Still counted (G9) |
| Weights don't total 100 | Rescaled automatically, so the score still works, but each goal's importance isn't what was intended |
| Min Acceptable and Max Possible | Not used in the calculation |
| Score and Actual Achievement typed on an employee plan item | **Not used** in the evaluation (G11) |
| Assigned duties, job title KPIs, tickets, attendance | **Not used** in the evaluation (G14). A duty's approval score is separate from task scores |

### Rating bands

**The system only stores a number and doesn't label it** (G13). Your institution should agree bands so that a score means the same in every department. This is a starting point for discussion, not an official scale. If you are bound by a government or institutional appraisal policy, that policy takes priority.

| Final score | Suggested rating |
|---|---|
| 90 to 100 | Outstanding |
| 75 to 89.99 | Very good |
| 60 to 74.99 | Good |
| 40 to 59.99 | Needs improvement |
| Below 40 | Unsatisfactory |

---

## Worked example

A registration officer has this plan for the year:

| Goal | Weight | Target | Verified achieved | Goal % | Points | Average task score | Task points |
|---|---|---:|---:|---:|---:|---:|---:|
| ONGEZEKO LA WAGONJWA, NHIF visits registered | 40 | 1,200 | 1,080 | 90 | 36.0 | 8 / 10 | 32.0 |
| ONGEZEKO LA WAGONJWA, cash visits registered | 30 | 800 | 880 | 100 (capped) | 30.0 | 9 / 10 | 27.0 |
| Monthly client feedback reviews completed | 30 | 12 | 9 | 75 | 22.5 | 7 / 10 | 21.0 |
| **Total** | **100** | | | | **88.5** | | **80.0** |

```text
Self score        = (88.5 / 100) x 100              = 88.50
Task score        = (80.0 / 100) x 100              = 80.00
Supervisor score  = entered after review            = 80.00
Final score       = (88.50 + 80.00 + 80.00) / 3     = 82.83
```

Using the suggested bands, this is **Very good**. The supervisor records that the cash target was beaten, and that the three missed feedback reviews are the improvement area for next period.

---

## Suggested appraisal calendar

The system has no calendar or reminders, and no mid-period review (G12). Run these steps as an institutional routine, adapting them to your financial year.

| When | Activity | Who | Output |
|---|---|---|---|
| Before the period starts | Approve the organizational plan | Top management | Active plan, weights 100% |
| Week 1 to 2 | Create and activate department plans | Department heads | Active department plans |
| Week 3 to 4 | Agree and activate employee plans | Supervisors, employees | SMART goals, agreed and active |
| Monthly | Record implementation | Employees | Up-to-date entries |
| Monthly or quarterly | Verify and score entries | Supervisors | Accurate progress and feedback for everyone |
| Quarterly | Check-in meeting | Supervisors, employees | Progress reviewed, obstacles noted |
| Mid-period | Formal mid-period review | Supervisors, employees | Written review, kept outside the system for now. Targets changed only with agreement |
| After the period ends | Submit self-evaluation | Employees | Evaluation submitted |
| Within 2 to 4 weeks | Clear pending entries, then review and approve each level | Approvers | Approved final scores |
| After approval | Share results, plan development | HR, supervisors | Input for training and next period's goals |

---

## Known gaps and recommended changes

Until each gap is closed, follow its workaround.

### Fixed

These four critical gaps are closed. Their numbers are kept so that older references still make sense.

| # | Was | Now |
|---|---|---|
| **G1** | With no approver configured, evaluations were still submitted and got stuck where no one could see them | Submission is refused until an approver exists for the employee. Stuck evaluations move to a valid level when an approver opens Approve Evaluations |
| **G2** | Submission always started at level `1` | Starts at the lowest level with an eligible approver |
| **G3** | Entries counted without verification, and evidence was text only | Supervisors verify or reject on Verify Implementations, and can score and comment. Only verified entries count, approval needs all entries cleared, and one evidence file can be attached |
| **G4** | Approvers could approve their own evaluation, and saw all departments | Self-approval and self-verification are blocked, and approvers only see the departments they cover |

### High: needed to make goals properly SMART

| # | Gap | SMART letter | Workaround | Recommended change |
|---|---|---|---|---|
| **G5** | No dates on department plans, employee plans or items. Entries aren't checked against the plan period, and evaluations can be submitted any time | **T** | State deadlines in the Description, and submit after the end date | Add due dates, restrict entries to the period, and open evaluations in a set window |
| **G6** | Target value is optional, and a goal without one scores 0 | **M** | Reviewers refuse goals without a target | Require a target and unit, or a defined scoring method |
| **G7** | KPI type, measurement type, rating scale and scoring criteria exist on organizational items, but **are lost when copied down**. Qualitative goals can't be scored properly | **M** | Rewrite as countable goals | Carry these fields down, and score rating-scale and yes/no goals properly |
| **G8** | Only "add up toward a higher number" is supported. Lower-is-better and percentage goals score wrongly, and Min Acceptable is unused | **M, A** | [Rewrite the goal](#writing-goals-so-the-score-comes-out-right) | Add a calculation method per goal: cumulative, latest value, lower-is-better, yes/no |
| **G9** | Weights only total-checked at organizational approval. The status field can skip approval, and inactive goals still count | **A** | Reviewers check 100%, and don't use the status shortcut | Enforce 100% at activation at each level, remove the shortcut, and exclude inactive goals |
| **G10** | No goal agreement step. Submit for Review activates immediately, and goals can still be added after activation | **A, R** | Agree before submitting, and record it in the description | Add submit → supervisor approval → locked goals, with changes needing re-approval |

### Medium: improves fairness and usefulness

| # | Gap | Recommended change |
|---|---|---|
| **G11** | Two unconnected score sources: the evaluation uses implementation entries, while Score/Actual typed on plan items are display-only. The numbers can disagree | Keep one source of truth, and remove or connect the other |
| **G12** | No mid-period review | Add a mid-period review record with comments from both sides |
| **G13** | No rating bands or labels | Add agreed, configurable bands, and show the rating with the score |
| **G14** | Assigned duties, job title KPIs and tickets are not part of the evaluation. Job title KPIs aren't used anywhere else either | Decide whether they count. If so, define their share of the score |
| **G15** | The department ranking on the Overview uses goal progress, not approved final scores, and ignores weights | Base it on final scores, or remove it |
| **G16** | No employee acknowledgement or formal appeal after scoring, and only the last level's score counts | Add employee sign-off, a disagreement record and an appeal route |
| **G17** | No reminders or notifications at any stage, and no export of appraisal results | Add notifications for submissions and decisions, plus PDF/Excel reports |
| **G18** | The Organizational Plans and Job Title KPI pages hide their menu links by permission, but don't check permission on the server, so a logged-in user could open them by URL | Add the same server-side checks used on Department and Employee Plans |

### Suggested order of work

1. **Keep one score source:** G11
2. **Make goals SMART by design:** G6, G5, G9, G10
3. **Score every type of goal fairly:** G7, G8, G13
4. **Complete the open appraisal cycle:** G12, G16, G14, G15, G17, G18

---

## Troubleshooting

| Problem | Likely cause | What to do |
|---|---|---|
| Can't see Department or Employee Plans from other departments | Access is limited to your department | Ask for `view-all-dept-plans` if your role needs it |
| No department plans listed for an employee | The department plan isn't Active, or the department has none | Activate the department plan (Step 2) |
| The department plan item picker offers nothing to select | All items are already in this employee plan, shown as "already added" | Add new items to the department plan, or create a custom goal |
| Can't approve the organizational plan | No items, or weights don't total exactly 100% | Add items, or correct the weights |
| Distribute button is missing | The plan already has items | Add the remaining items by hand |
| Plan doesn't appear under plans ready for evaluation | It's still Draft, or already has an open or approved evaluation | Activate it, or check My Evaluation |
| "No approver is set up for your department" when submitting | No level mapped to `Performance` has an eligible approver for you | Finish [setup](#before-you-start-system-setup). If you are the only approver at every level, add another |
| An approver can't see an evaluation | It's at another level, or they don't cover that department | Check level and department coverage under [setup](#before-you-start-system-setup) |
| Can't approve: "Verify or reject all pending entries" | The employee still has pending entries | Use the link in the form to clear them, then approve |
| **Verify Implementations** missing from the menu | You aren't an approver or `manage-employee-plans` holder, or have no linked employee record | Ask the administrator for access |
| An entry isn't listed for you to verify | It's your own, or it's outside the departments or plans you can see | Another verifier must handle it |
| File won't upload | Wrong type, or larger than 10 MB | Use PDF, image, Word or Excel, up to 10 MB |
| **Approve Evaluations** missing from the menu | You aren't assigned to a level mapped to `Performance` | Ask the administrator to assign you |
| Self score looks too low | Entries not yet verified, goals with no target, entries with no quantity, or targets not reached | Check each goal against [the calculation](#how-the-score-is-calculated) |
| Self score looks too high | Verified entries include running totals rather than new work | The verifier rejects those entries, and the employee corrects them |
