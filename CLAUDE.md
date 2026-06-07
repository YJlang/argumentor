# CLAUDE.md

This file orients Claude Code (and any agent) working in this repository. It is loaded
automatically at the start of a session. Keep it short, current, and honest.

## What this project is

**AI 논리 토론 시뮬레이터** (AI Logical Debate Simulator) — a debate-training web service,
**not** a generic chat app. The user enters a topic and stance; the AI argues the opposite
side, then the system analyzes the user's logical structure, weaknesses, and improvement
strategy, and stores sessions for later review.

The defining loop is **simulation + analysis**, in three stages:
1. User states a claim (or the AI drafts one)
2. AI generates counter-arguments and challenge questions
3. The system analyzes logic structure, weaknesses, and improvement strategies

Full product scope, screens, data model, and API flow live in **[PLAN.md](PLAN.md)** — that is
the source of truth for *what* to build. This file covers *how* we build it.

## Stack (decided — do not drift)

| Layer | Choice | Notes |
|---|---|---|
| Backend | **PHP** | Vanilla PHP SSR. No SPA backend-for-frontend. |
| Rendering | **PHP server-side rendering** | Server-rendered templates + small progressive-enhancement JS. Not a React runtime. |
| Database | **MySQL / MariaDB** | Via XAMPP. Use PDO with prepared statements. |
| LLM | **OpenAI API** | Called from PHP: build prompt → call API → parse JSON → store → return. |
| Frontend look | Reference only | `LogicalAI/` is a Figma-exported React prototype used as a **visual/style reference**, translated into PHP templates. |

**Do not** convert this project to a React/Next SPA, swap the database, or introduce a heavy
PHP framework without the user explicitly changing direction. Conservative, stack-aligned
choices only.

## Current phase

**Planning / setup — implementation has NOT started.** No production PHP, schema, or API code
exists yet. Do not begin application implementation unless the user explicitly asks for code.
When asked to implement, follow PLAN.md and the conventions below.

## Repository layout

```
htdocs/                         # git root (XAMPP web root — see .gitignore)
├── CLAUDE.md                   # this file — agent guide
├── AGENTS.md                   # short cross-agent pointer to this file
├── PLAN.md                     # product spec (source of truth for scope)
├── docs/
│   └── SKILLS.md               # installed agent skills + how to use them
├── LogicalAI/                  # React UI prototype — VISUAL REFERENCE ONLY (do not ship)
│   └── src/app/components/     # LandingPage, DebateSetup, Simulation, AnalysisDashboard
└── .claude/skills/             # project-scoped Claude Code skills
```

XAMPP default files (`xampp/`, `dashboard/`, `webalizer/`, `applications.html`, root
`index.php`, etc.) are **not part of the project** and are git-ignored.

### Planned (create only when implementation begins)
`docs/ARCHITECTURE.md`, `docs/DB_SCHEMA.md`, `docs/API_SPEC.md`, `docs/UI_MAPPING.md`.

## Commands

PHP and MySQL are **not on PATH**; they live inside XAMPP. From a Windows shell:

```powershell
# Run the prototype (React reference UI)
cd LogicalAI; npm install; npm run dev      # Vite dev server

# PHP (once production code exists) — XAMPP binaries
C:\xampp\php\php.exe -v
C:\xampp\php\php.exe -S localhost:8000       # quick dev server from a chosen docroot

# MySQL CLI
C:\xampp\mysql\bin\mysql.exe -u root
```

Normal serving is via XAMPP Apache pointing at this `htdocs`. Start Apache + MySQL from the
XAMPP Control Panel.

## Conventions for production PHP (when we get there)

- `declare(strict_types=1);` at the top of every PHP file; type-hint params/returns; PSR-12.
- **DB access:** PDO + prepared/parameterized queries only. Never concatenate user input into SQL.
- **Secrets:** OpenAI API key and DB credentials in environment / a git-ignored config file
  (`config.local.php` or `.env`), never hardcoded, never committed.
- **Output:** escape all dynamic output with `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
  Treat XSS, CSRF, and injection as first-class concerns (see the `web-app-security-audit` skill).
- **Structure:** keep business logic out of templates; prefer small reusable PHP partials /
  view includes over a monolith.
- **LLM calls:** version prompt templates; validate/parse the model's JSON; never interpolate
  user input into a system prompt (prompt-injection); store raw + parsed results per PLAN.md's
  `debates` / `debate_results` tables.
- **SEO:** prioritize for public pages (landing, public detail). Internal simulation/analysis
  screens do not need SEO-first tradeoffs.

## Agent behavior

- Read **PLAN.md** before implementing a feature; treat **LogicalAI/** as look-and-feel
  reference to translate into PHP SSR — not as code to ship or keep in React.
- Use the installed skills when relevant (`php-pro`, `database-schema-design`, `llm-engineering`,
  `web-app-security-audit`, `analyse-with-phpstan`). See [docs/SKILLS.md](docs/SKILLS.md).
- For OpenAI API specifics, prefer official OpenAI documentation over memory. There is no
  OpenAI Docs MCP in this environment — use `WebFetch`/`WebSearch` (or the `exa` MCP) against
  `https://platform.openai.com/docs` and cite what you rely on.
- Keep changes conservative and aligned with the stack table above.
- Commit/push only when the user asks.
