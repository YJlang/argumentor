# Agent Skills

Project-scoped Claude Code skills live in `.claude/skills/`. They are picked up automatically
and invoked by the agent when a task matches their description (or via `/<skill-name>`).

These were selected for this project's stack (vanilla PHP SSR + MySQL + OpenAI API) and
installed from the SkillsMP marketplace.

## Installed skills

| Skill | Use it for | Source |
|---|---|---|
| **php-pro** | Writing modern PHP 8.3+ — strict types, PSR-12, typed DTOs/services, enums, PHPUnit tests. | `Jeffallan/claude-skills` |
| **database-schema-design** | Designing the `debates` / `debate_results` tables, relationships, indexes, and MySQL migrations. | `aiskillstore/marketplace` |
| **llm-engineering** | OpenAI API integration patterns: prompt templates, structured-output validation, retries, token/cost control, evals. (Examples are Python/Anthropic-flavored; apply the patterns to PHP + OpenAI.) | `davekim917/bootstrap` |
| **web-app-security-audit** | Auditing PHP/JS for XSS, SQL injection, CSRF, auth/session issues, headers, secrets, dependencies. Run before shipping public pages. | `peterbamuhigire/skills-web-dev` |
| **analyse-with-phpstan** | Static analysis of PHP snippets/files via the PHPStan playground API across PHP 7.2–8.5. | `phpstan/phpstan` (official) |

## Reproducing this setup in another environment

After `git pull`, the skills are **already present** in `.claude/skills/` (they are committed to the
repo) — Claude Code picks them up automatically, no install step needed.

If you want to **reinstall or update them from source** via the `skillsmp` MCP server, use the
exact coordinates below. (The SkillsMP `install_skill` tool currently forwards to a deprecated
CLI and does not finish the install, so use the **fetch-then-write** method.)

### Source coordinates

| Skill | owner | repo | path | branch |
|---|---|---|---|---|
| php-pro | `Jeffallan` | `claude-skills` | `skills/php-pro` | `main` |
| database-schema-design | `aiskillstore` | `marketplace` | `skills/supercent-io/database-schema-design` | `main` |
| llm-engineering | `davekim917` | `bootstrap` | `plugins/domain/skills/llm-engineering` | `main` |
| web-app-security-audit | `peterbamuhigire` | `skills-web-dev` | `skills/security/web-app-security-audit` | `main` |
| analyse-with-phpstan | `phpstan` | `phpstan` | `.claude/skills/analyse-with-phpstan` | `2.2.x` |

### Install procedure (fetch-then-write)

For each skill, ask the agent to run the `skillsmp` MCP tool `get_skill_content` with that row's
`owner` / `repo` / `path` / `branch`, then write the returned content to
`.claude/skills/<skill-name>/SKILL.md`. One prompt that does all five:

```
Reinstall this project's agent skills from source. For each row in docs/SKILLS.md
"Source coordinates", call the skillsmp MCP tool get_skill_content with its
owner/repo/path/branch, and write the returned SKILL.md to
.claude/skills/<skill-name>/SKILL.md. Then confirm all five exist.
```

Requirements: the `skillsmp` MCP server must be connected. Note `analyse-with-phpstan` lives on
branch **`2.2.x`** (not `main`) — passing the wrong branch returns a directory listing instead of
the file.

> Local copies in `.claude/skills/` are lightly adapted for this project (e.g. MySQL-flavored
> examples in `database-schema-design`, a PHP+OpenAI note in `llm-engineering`). Re-fetching from
> source overwrites those tweaks with the upstream originals — re-apply if needed, or just keep the
> committed copies.

## How they map to the build

- **Schema first** → `database-schema-design` to lock the MySQL schema from PLAN.md §8.
- **Backend** → `php-pro` for controllers/services/DTOs; `analyse-with-phpstan` to catch type bugs.
- **AI layer** → `llm-engineering` for prompt design, JSON parsing, and cost/latency discipline.
- **Before release** → `web-app-security-audit` on the public-facing pages.

## Notes

- Installation was done manually (writing each `SKILL.md` into `.claude/skills/<name>/`) because
  the SkillsMP MCP `install` command currently forwards to a deprecated CLI and does not
  complete the install itself. The `get_skill_content` / `search` MCP tools work fine.
- A few skills reference a `references/` subdirectory for deep-dive detail. Only the core
  `SKILL.md` is installed here; that is sufficient for day-to-day use. If you need a skill's
  reference files, fetch them with the SkillsMP `get_skill_content` tool.
- To add more skills later: search with the `skillsmp` MCP, preview with `get_skill_content`,
  then write the returned `SKILL.md` into a new `.claude/skills/<name>/` folder.

## OpenAI documentation

There is **no OpenAI Docs MCP** in this environment. For OpenAI API guidance, use `WebFetch` /
`WebSearch` (or the `exa` MCP) against the official docs at `https://platform.openai.com/docs`
and prefer them over memory.
