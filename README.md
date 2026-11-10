# ResidentialProperFollowFlow — AI Lead Follow-Up Autopilot for Residential Property Managers

**ResidentialProperFollowFlow** is a modular Laravel + Filament SaaS that helps residential property manager owners and operations leads **generate and send consent-aware follow-ups**, **escalate qualified replies to staff**, and operate on **properties, residents, maintenance requests, and vendor work orders**.

Built for the USA Real Estate & Property sector — sales operations workflow with human-in-the-loop AI drafts, TCPA-aware messaging consent, fair-housing awareness, quiet hours, and a full ON/OFF module catalog.

## Local demo

- **App URL:** http://localhost:8124
- **Admin:** http://localhost:8124/admin
- **MySQL:** localhost:33094
- **Redis:** localhost:63814

```bash
cd pipeline/apps/ResidentialProperFollowFlow
docker compose up -d --build
```

Entrypoint runs `migrate` + `db:seed` automatically against MySQL (not SQLite).

## Demo users

Password for all: `password`

| Role | Email |
|------|-------|
| Admin | admin@residentialproperfollowflow.test |
| Owner | owner@residentialproperfollowflow.test |
| Staff | staff@residentialproperfollowflow.test |
| Technician / Vendor | tech@residentialproperfollowflow.test |
| Customer | customer@residentialproperfollowflow.test (no admin panel) |

## Public pages

- `/` — Home / product story
- `/features` — Full feature catalog
- `/modules` — Every module ON/OFF
- `/workflow` — MVP intake → draft → approve → audit
- `/pricing` — Pilot $199–$799/mo
- `/demo` — Live lead intake form
- `/contact` — Hire / contact
- `/admin` — Filament admin

## Core workflow (MVP)

1. **Intake trigger** — website / phone / email / SMS / referral with consent flags  
2. **AI classify + draft** — leasing vs maintenance category, priority, score, Day-0 follow-up  
3. **Human approval** — staff/owner gate; consent + quiet hours can block/defer  
4. **Send + await reply** — audited send; cadence stamp  
5. **Escalate qualified replies** — SLA escalation → CRM opportunity + maintenance request  
6. **Vendor work orders** — dispatch vendors with safety checklists  

## Modules

Toggle in Filament **Module Settings** (synced from `config/modules.php`).

**Core ON:** Lead Follow-Up Autopilot, Messaging Consent Guard, AI Follow-Up Writer, Human Approval, Reply Escalation, Sequences, Inbound Lead Intake, AI Classification, CRM Opportunities, Properties, Residents & Prospects, Maintenance Requests, Vendors, Vendor Work Orders, Audit Log, Quiet Hours, Fair Housing Guard, AI Hub, Drutogoti Speed, Admin Panel Extras, Slug Engine.

**Niche OFF by default:** Resident Portal, Attendance Hub, Unicode PDF Exports, AI Catalog Scrape, AppFolio, Buildium, Email Integration, Accounting Integration, Multi-Property Portfolio.

> Packagist `imrandevbd/*` packages are not required as stable Composer deps for Laravel 13 — local service equivalents ship for AI Hub, Admin Panel extras, Slug, Attendance, Unicode PDF, AI Catalog, and Drutogoti speed helpers.

## Tech stack

- PHP 8.4 · Laravel 13 · Filament 4  
- MySQL 8.4 · Redis 7  
- Docker Compose (`ext-intl` installed for Filament)  
- Modular feature flags · Multi-role users · AI assist hooks  

## Screenshots

![ResidentialProperFollowFlow home — residential property lead follow-up autopilot](docs/screenshots/home.png)

![Features catalog — consent, AI drafts, escalation, properties and vendors](docs/screenshots/features.png)

![Modules ON/OFF — modular Laravel Filament property management SaaS](docs/screenshots/modules.png)

![Filament admin dashboard — leads, drafts, escalations, work orders](docs/screenshots/admin-dashboard.png)

![Follow-up drafts — human approval gate for consent-aware messaging](docs/screenshots/followup-drafts.png)

![Admin login — multi-role demo users](docs/screenshots/admin-login.png)

## Related keywords

Residential property managers, residential property lead follow-up, leasing tour nurture, lease application follow-up, consent-aware SMS, TCPA property management messaging, fair housing messaging guard, tenant privacy audit log, email drip leasing, sales operations automation, property management CRM, AppFolio follow-up workflow, Buildium maintenance escalation, accounting property leads, multifamily leasing automation, resident maintenance requests, vendor work orders, Laravel Filament SaaS, AI business automation, modular feature flags, multi-role admin owner staff technician customer, quiet hours messaging, real estate & property USA 2026, lead follow-up autopilot, reply escalation SLA, human-in-the-loop AI drafts, residential property managers owner operations lead.

## Integrations (module toggles)

AppFolio · Buildium · Email · Accounting

## Risk / compliance

MED/HIGH — housing, fair-housing and tenant privacy. Consent guard, quiet hours, fair-housing flag, and immutable audit log are ON by default for the demo.

## Author

Git commits are authored only as **Imran Ahmed &lt;me@imrandev.bd&gt;**. No GitHub Actions workflows ship with this repo.

## Contact / Hire

- Developer: Imran Ahmed (IMRAN Dev BD)
- Website: https://imrandev.bd/contact
- Email: me@imrandev.bd
- GitHub: https://github.com/imranbru99
