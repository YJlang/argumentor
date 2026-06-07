# AGENTS.md

Cross-tool entry point for AI coding agents. The full, authoritative guide is **[CLAUDE.md](CLAUDE.md)** —
read it first. This file exists so non-Claude agents (Codex, Cursor, etc.) are oriented too.

## TL;DR

- **Project:** AI 논리 토론 시뮬레이터 — a debate-training tool (simulation + analysis), not a chat app.
- **Scope / source of truth:** [PLAN.md](PLAN.md)
- **Stack (do not drift):** PHP + MySQL + **PHP SSR**. OpenAI API for the LLM.
- **`LogicalAI/`:** React/Figma prototype = **visual reference only**, translated into PHP templates. Never shipped as the runtime.
- **Phase:** planning/setup — do **not** start implementation unless explicitly asked.

## Rules of engagement

1. Read [CLAUDE.md](CLAUDE.md) and [PLAN.md](PLAN.md) before writing code.
2. Keep implementation conservative and aligned to PHP + MySQL + SSR. No SPA rewrite, no DB swap, no heavy framework without the user changing direction.
3. PDO prepared statements; escape all output; secrets in env / git-ignored config, never committed.
4. For OpenAI API behavior, prefer official OpenAI docs over memory (use web search/fetch — no OpenAI Docs MCP in this environment).
5. Commit/push only when asked.

Conventions, commands, layout, and skill usage: see [CLAUDE.md](CLAUDE.md) and [docs/SKILLS.md](docs/SKILLS.md).
