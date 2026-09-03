# Legacy X Firm — Project Checkpoint

**Saved:** September 3, 2026  
**Repository:** donteck/Legacy-x-firm  
**Production:** https://legacyxfirm.us/

## Canonical Direction
The current production direction is the **Legacy X Firm Luxury Private Advisory** platform. Earlier Consultio/legacy homepage concepts are deprecated and should be treated only as historical recovery material. The current luxury build targets established business owners, founders, executives, investors, real-estate principals, family enterprises, and high-net-worth audiences.

Core positioning: **Private Business & Capital Strategy** — *Build wealth. Structure power. Protect legacy.*

## Architecture Progress

1. Luxury Public Website — complete
2. Executive Assessment — complete
3. Universal Client Profile / Legacy X ID — complete
4. Client Account Linkage & Authentication Foundation — complete
5. Client Command Center — complete
6. CreditOS — complete
7. Business Center — complete
8. Documents & Tasks — complete
9. Applications Center — complete
10. Billing & Payments — complete
11. Staff Executive Command Center + Client 360 — active/maturing
12. Services / Engagement Management — mostly built
13. Appointments & Messaging — built foundation
14. Staff Roles & Permissions — deployed; further AI-specific hardening remains advisable
15. Reports & Analytics — deployed
16. Legacy X Firm × Elearnz OS — deferred until Elearnz is ready
17. Legacy X Intelligence — complete
18. Automation Engine — product milestone complete; trusted-data hardening remains on backlog
19. Legacy X AI Advisor — active development; provider architecture deployed, secure server configuration is the current checkpoint

## Current Operating Flow

LegacyXFirm.us → Executive Assessment → Universal Client Profile / Legacy X ID → Client Account → Client OS → Services / Advisory → Reports → Legacy X Intelligence → Automation → Legacy X AI Advisor

Future Elearnz integration:

Legacy X → Elearnz Enrollment / Link → Elearnz Learning → Progress / Completion Sync → Client 360 / Firm OS

## Client Portal

Production client portal modules:
- Strategic Overview
- My Services
- Credit & Capital
- Business Center
- Documents & Tasks
- Applications
- Billing & Payments
- Appointments & Messages

## Staff / Firm OS

Current management architecture includes:
- Executive Command Center
- Client Directory
- Client 360
- Engagement / Service Management
- Communications
- Workflow
- Applications
- Billing
- Reports & Executive Intelligence
- Legacy X Intelligence
- Client Intelligence Watchlist
- Executive Intelligence Briefing
- Intelligence Action Center
- Automation Engine
- Legacy X AI Advisor

## Phase 17 — Legacy X Intelligence

Deployed capabilities include:
- Firm-level operating intelligence
- Client Relationship Intelligence
- Client Intelligence Watchlist
- Intelligence trends
- Executive Intelligence Briefing
- Intelligence Action Center

The intelligence layer is deterministic/explainable management support. It is not lender underwriting, investment advice, legal advice, a credit decision engine, or a guarantee/prediction system.

## Phase 18 — Automation Engine

Deployed foundation includes:
- Automated operational rule scanning
- Management action generation
- Routing and reminders
- Escalation
- Automation audit center
- Manager/Admin automation permissions

Backlog before relying on automation for high-impact external AI context:
- Revalidate billing status schema
- Revalidate application status/client relation schema
- Improve unresolved-action deduplication lifecycle
- Ensure newly created automation actions route after source metadata exists
- Recheck reminder capability boundaries
- Ensure escalation only affects intended automation actions and never downgrades Critical priority
- Align all automation handlers/admin screens consistently to the automation capability

## Phase 19 — Legacy X AI Advisor

### Deployed Foundation
- Executive Advisory Workspace
- Deterministic Advisor context engine
- Firm-level and capability-aware client context foundation
- Private per-user Advisor session memory
- Human-approved Recommendation → Intelligence Action bridge
- AI Provider Governance Gateway
- Explicit external-AI consent control
- Metadata-only AI governance audit
- Provider-neutral adapter boundary
- Intent-based model-routing profiles
- Governed OpenAI provider adapter code

### Governance Chain

AI Advisor → Consent → Data Minimization → Governance Gateway → Intent Classification → Model Profile → Approved Provider Adapter → Audit → Human Approval

### Security Rules
- No API keys in this public repository.
- No API keys in browser-side code.
- No customer/client private data, database dumps, uploads, backups, passwords, bank credentials, SSNs, full tax IDs, lender passwords, or private underwriting files in GitHub.
- External AI remains independently controlled by server-side configuration and authorized-user consent.
- Human approval remains required before Advisor recommendations become Intelligence Actions.
- Do not represent the Advisor as making autonomous funding, credit, investment, underwriting, or legal decisions.

## Current Phase 19 Checkpoint

The OpenAI/provider architecture has been deployed through GitHub Actions and the production theme verification workflow. The project is intentionally paused before entering any private API credential.

### Resume Instruction
When work resumes, open the Hestia terminal and continue with **secure server-side OpenAI configuration**. Do not paste the API key into ChatGPT and do not commit it to GitHub.

After the server secret/configuration is installed, continue with:
1. Verify provider/gateway readiness without exposing the key.
2. Enable the governed external-AI path.
3. Connect the live Ask Legacy X Advisor interface to the provider adapter.
4. Verify consent gating.
5. Verify outbound context minimization.
6. Verify response parsing and safe fallback to Foundation Mode.
7. Verify metadata-only governance audit.
8. Verify Recommendation → Human Approval → Intelligence Action.
9. Perform end-to-end testing with non-sensitive test data.
10. Keep deterministic Foundation Mode available if the provider is unavailable.

## Important Hardening Backlog

Before considering Phase 19 production-complete:
- Add/use a dedicated `legacyx_use_ai_advisor` capability for Manager/Admin rather than relying on reports access.
- Apply that capability consistently across Advisor UI, context, memory, gateway, audit, and provider adapter.
- Require both AI Advisor access and Intelligence Action management permission for action creation.
- Validate selected client IDs before attaching an AI-approved action.
- Strictly validate due-date format in the action bridge.
- Add recursive sensitive-key validation/redaction before external transmission.
- Apply prompt minimization/length controls and sensitive-data safeguards.
- Enforce HTTPS and approved provider hostnames before external requests.
- Keep API credentials server-side and out of logs/UI.
- Do not transmit local Advisor session memory externally unless an explicit governance decision authorizes it.
- Complete Phase 18 trusted-data preflight before AI depends heavily on automation-derived operational conclusions.

## Production Interaction Rule
Every visible button, CTA, card action, Open/Review/Start/Manage link, portal link, and dashboard control must connect to a real destination or real action. No decorative dead buttons, `href="#"`, empty links, or fake production actions.

## Deployment Rules
- Main production branch: `main`
- GitHub Actions deploys the Legacy X Firm theme to Hestia.
- PHP lint is mandatory before deployment.
- Production theme activation/verification is part of the workflow.
- Preserve rollback/recovery history.
- Never expose Hestia host/IP, private SSH keys, database credentials, or provider API secrets.

## Approved Recovery Reference
The approved luxury direction was previously preserved as `best/luxury-home-approved-2026-09-02`. Historical branches should be treated as recovery references, not active design direction.

## Resume Phrase
**“terminal open”** = resume Phase 19 from secure Hestia/OpenAI server configuration.
