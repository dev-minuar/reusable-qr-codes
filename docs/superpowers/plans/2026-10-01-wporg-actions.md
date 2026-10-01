# WordPress.org publishing via GitHub Actions — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Publish any minuar WordPress.org plugin by pushing a `vX.Y.Z` git tag, with release checks first; update readme/assets by a manual button.

**Architecture:** One public repo `dev-minuar/wporg-release` holds two reusable workflows (no secrets inside). Each plugin repo adds two short caller workflows, a `.wordpress-org/` folder with its directory assets, and two repository secrets. Deploy runs on tag push; readme/asset sync runs only on manual dispatch, because the 10up asset action publishes whatever is on `main`.

**Tech Stack:** GitHub Actions, `10up/action-wordpress-plugin-deploy@stable` (2.3.0), `10up/action-wordpress-plugin-asset-update@stable` (2.2.0), `WordPress/plugin-check-action@v1` (1.1.9), bash, curl, gh CLI.

**Spec (decided with Renato 2026-10-01, standing rule "take the recommended option"):**
- Option 2 of the publishing discussion: GitHub Actions, reusable across plugins.
- Secrets are repository secrets `SVN_USERNAME` / `SVN_PASSWORD` per plugin repo (least privilege; GitHub Free cannot give org secrets to private repos; the gh token has no `admin:org` scope). The password is read from the macOS Keychain item (account `minuar`, service `<https://plugins.svn.wordpress.org:443> Use your WordPress.org login`) and piped into `gh secret set` — never printed.
- Git tags are `vX.Y.Z`; SVN tags are `X.Y.Z` (strip the `v`).
- Checks before any SVN commit: tag version = plugin header `Version:` = readme `Stable tag:`; `php -l` on every shipped PHP file; WordPress.org readme validator (by URL) with no "Fatal" or "Warnings" section; Plugin Check on the distributable files with no errors.
- After a real deploy, poll the WordPress.org API until it reports the new version (max 30 min); the job fails if it never does.
- Assets live in git at `.wordpress-org/` and are excluded from the plugin ZIP via `.distignore`.

## Global Constraints

- New repo: `dev-minuar/wporg-release`, PUBLIC, default branch `main`, tag `v1` pointing at the working version. It must contain no secret.
- Plugin repo: `/Users/benne-air/projects/reusable-qr-codes-svn/trunk` (git, remote `dev-minuar/reusable-qr-codes`, branch `main`). The parent folder is an SVN working copy: never run `svn commit`/`svn copy` and never edit SVN properties in this plan.
- Never print the SVN password: no `echo`, no `-x` tracing, no command-line argument containing it. `gh secret set NAME --repo R` reads the value from stdin when `--body` is omitted, so pipe it in. Use exactly: `security find-generic-password -a minuar -s '<https://plugins.svn.wordpress.org:443> Use your WordPress.org login' -w | gh secret set SVN_PASSWORD --repo dev-minuar/reusable-qr-codes`.
- Commits: conventional prefix, imperative subject ~65 chars, body = what and why, NO Co-Authored-By or other trailers. Stage files by name.
- Do not push any tag on `reusable-qr-codes` (a `v*` tag push deploys for real). All deploy tests use `workflow_dispatch` with `dry-run: true`.
- Never spawn subagents.

## Review Focus

1. A tag whose version does not match the plugin header or readme → the job fails before any SVN step (Task 1 Step 4 test).
2. `workflow_dispatch` dry run → no SVN commit, no API polling, and it works with no secrets needed for the checks (Task 2 Step 5).
3. Files excluded by `.distignore` (`.wordpress-org`, `docs`, `AGENTS.md`, `CLAUDE.md`, `.github`) never reach the SVN trunk in the dry-run file list (Task 2 Step 5).
4. A private plugin repo: raw readme URL is not public → the validator step must skip with a warning instead of failing (Task 1 code; Task 2 notes it).
5. The secret never appears in any log: the workflow passes it only via `env:` to the 10up actions (Task 1 code review).

---

### Task 1: Reusable workflows repo `dev-minuar/wporg-release`

**Files (new repo, cloned to `/Users/benne-air/projects/wporg-release`):**
- Create: `.github/workflows/deploy.yml`, `.github/workflows/assets.yml`, `README.md`, `LICENSE` (MIT)

**Interfaces:**
- Produces: `dev-minuar/wporg-release/.github/workflows/deploy.yml@v1` — `on: workflow_call` with inputs `version` (string, optional; default = tag name without leading `v`), `slug` (string, optional; default = repo name), `dry-run` (boolean, default false), `plugin-check` (boolean, default true); secrets `SVN_USERNAME`, `SVN_PASSWORD` (required: false, so dry runs work without them).
- Produces: `dev-minuar/wporg-release/.github/workflows/assets.yml@v1` — `on: workflow_call` with input `slug` (optional); secrets `SVN_USERNAME`, `SVN_PASSWORD` (required: true).

- [ ] **Step 1: Create the repo**

```bash
cd /Users/benne-air/projects && gh repo create dev-minuar/wporg-release --public --description "Reusable GitHub workflows to publish WordPress plugins to WordPress.org" --clone && cd wporg-release
```

- [ ] **Step 2: `.github/workflows/deploy.yml`**

```yaml
name: Deploy to WordPress.org

on:
  workflow_call:
    inputs:
      version:
        description: 'Version to deploy (defaults to the git tag without a leading v)'
        type: string
        required: false
        default: ''
      slug:
        description: 'WordPress.org plugin slug (defaults to the repository name)'
        type: string
        required: false
        default: ''
      dry-run:
        description: 'Run every step except the SVN commit'
        type: boolean
        required: false
        default: false
      plugin-check:
        description: 'Run Plugin Check on the distributable files'
        type: boolean
        required: false
        default: true
    secrets:
      SVN_USERNAME:
        required: false
      SVN_PASSWORD:
        required: false

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Resolve version and slug
        id: meta
        shell: bash
        run: |
          set -euo pipefail
          version="${{ inputs.version }}"
          if [ -z "$version" ]; then
            version="${GITHUB_REF_NAME#v}"
          fi
          slug="${{ inputs.slug }}"
          if [ -z "$slug" ]; then
            slug="${GITHUB_REPOSITORY#*/}"
          fi
          echo "version=$version" >> "$GITHUB_OUTPUT"
          echo "slug=$slug" >> "$GITHUB_OUTPUT"
          echo "Deploying $slug $version (dry run: ${{ inputs.dry-run }})"

      - name: Check version numbers match
        shell: bash
        run: |
          set -euo pipefail
          version="${{ steps.meta.outputs.version }}"
          main_file="$(grep -l -E '^[[:space:]/*#]*Plugin Name:' ./*.php | head -n 1)"
          if [ -z "$main_file" ]; then
            echo "::error::No PHP file with a 'Plugin Name:' header in the repository root."
            exit 1
          fi
          header="$(grep -m 1 -E '^[[:space:]/*#]*Version:' "$main_file" | sed -E 's/.*Version:[[:space:]]*//' | tr -d '\r[:space:]')"
          stable="$(grep -m 1 -i -E '^Stable tag:' readme.txt | sed -E 's/^[Ss]table [Tt]ag:[[:space:]]*//' | tr -d '\r[:space:]')"
          echo "Tag: $version | $main_file Version: $header | readme Stable tag: $stable"
          if [ "$version" != "$header" ] || [ "$version" != "$stable" ]; then
            echo "::error::Version mismatch: tag $version, plugin header $header, readme Stable tag $stable."
            exit 1
          fi

      - name: PHP syntax check
        shell: bash
        run: |
          set -euo pipefail
          find . -name '*.php' -not -path './vendor/*' -not -path './node_modules/*' -not -path './.git/*' -print0 \
            | xargs -0 -n 1 php -l > /tmp/php-lint.log || { cat /tmp/php-lint.log; exit 1; }
          echo "$(grep -c 'No syntax errors' /tmp/php-lint.log) PHP files pass php -l"

      - name: WordPress.org readme validator
        shell: bash
        run: |
          set -euo pipefail
          raw="https://raw.githubusercontent.com/${GITHUB_REPOSITORY}/${GITHUB_SHA}/readme.txt"
          if [ "$(curl -s -o /dev/null -w '%{http_code}' "$raw")" != "200" ]; then
            echo "::warning::readme.txt is not publicly reachable (private repository?); skipping the online validator."
            exit 0
          fi
          enc="$(python3 -c 'import sys, urllib.parse; print(urllib.parse.quote(sys.argv[1], safe=""))' "$raw")"
          curl -s "https://wordpress.org/plugins/developers/readme-validator/?readme=${enc}" -o /tmp/validator.html
          python3 - <<'PY'
          import html, re, sys
          page = open('/tmp/validator.html', encoding='utf-8').read()
          start = page.find('Readme Validator</h1>')
          end = page.find('or paste your', start)
          text = html.unescape(re.sub(r'<[^>]+>', '\n', page[start:end] if start >= 0 else ''))
          lines = [l.strip() for l in text.splitlines() if l.strip()]
          print('\n'.join(lines))
          bad = [l for l in lines if l.startswith('Fatal') or l.startswith('Warnings')]
          if start < 0:
              print('::warning::Validator page format not recognised; result not checked.')
          elif bad:
              print('::error::The readme validator reported: ' + ', '.join(bad))
              sys.exit(1)
          PY

      - name: Build distributable copy
        if: ${{ inputs.plugin-check }}
        shell: bash
        run: |
          set -euo pipefail
          mkdir -p /tmp/dist
          if [ -f .distignore ]; then
            rsync -a --exclude-from=.distignore --exclude=.git ./ "/tmp/dist/${{ steps.meta.outputs.slug }}/"
          else
            rsync -a --exclude=.git --exclude=.github ./ "/tmp/dist/${{ steps.meta.outputs.slug }}/"
          fi
          rm -rf "./.wporg-dist" && mv /tmp/dist "./.wporg-dist"

      - name: Plugin Check
        if: ${{ inputs.plugin-check }}
        uses: WordPress/plugin-check-action@v1
        with:
          build-dir: './.wporg-dist/${{ steps.meta.outputs.slug }}'
          ignore-warnings: 'true'

      - name: Remove distributable copy
        if: ${{ always() && inputs.plugin-check }}
        shell: bash
        run: rm -rf ./.wporg-dist

      - name: Deploy to WordPress.org
        uses: 10up/action-wordpress-plugin-deploy@stable
        with:
          dry-run: ${{ inputs.dry-run }}
        env:
          SVN_USERNAME: ${{ secrets.SVN_USERNAME }}
          SVN_PASSWORD: ${{ secrets.SVN_PASSWORD }}
          SLUG: ${{ steps.meta.outputs.slug }}
          VERSION: ${{ steps.meta.outputs.version }}

      - name: Wait until WordPress.org serves the new version
        if: ${{ !inputs.dry-run }}
        shell: bash
        run: |
          set -euo pipefail
          slug="${{ steps.meta.outputs.slug }}"; version="${{ steps.meta.outputs.version }}"
          for i in $(seq 1 60); do
            live="$(curl -s "https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request%5Bslug%5D=${slug}&nocache=${RANDOM}" | python3 -c 'import json,sys; print(json.load(sys.stdin).get("version",""))' || true)"
            if [ "$live" = "$version" ]; then
              echo "WordPress.org now serves $slug $version: https://wordpress.org/plugins/$slug/"
              exit 0
            fi
            sleep 30
          done
          echo "::error::WordPress.org still serves '$live' after 30 minutes."
          exit 1
```

The scratch folder `.wporg-dist` is removed before the deploy step, so the 10up action never sees it.

- [ ] **Step 3: `.github/workflows/assets.yml`**

```yaml
name: Update WordPress.org readme and assets

on:
  workflow_call:
    inputs:
      slug:
        description: 'WordPress.org plugin slug (defaults to the repository name)'
        type: string
        required: false
        default: ''
    secrets:
      SVN_USERNAME:
        required: true
      SVN_PASSWORD:
        required: true

jobs:
  assets:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Resolve slug
        id: meta
        shell: bash
        run: |
          slug="${{ inputs.slug }}"
          if [ -z "$slug" ]; then slug="${GITHUB_REPOSITORY#*/}"; fi
          echo "slug=$slug" >> "$GITHUB_OUTPUT"

      - name: Push readme and assets to WordPress.org
        uses: 10up/action-wordpress-plugin-asset-update@stable
        env:
          SVN_USERNAME: ${{ secrets.SVN_USERNAME }}
          SVN_PASSWORD: ${{ secrets.SVN_PASSWORD }}
          SLUG: ${{ steps.meta.outputs.slug }}
          IGNORE_OTHER_FILES: true
```

- [ ] **Step 4: `README.md` and `LICENSE`**

README sections (plain, short): what the two workflows do; the caller files to copy into a plugin repo (the exact YAML from Task 2 Step 3); the one-time secret setup command (the keychain pipe from Global Constraints, with a placeholder repo name); how to release (`git tag vX.Y.Z && git push origin vX.Y.Z`); how to update readme/assets only (Actions tab → "WordPress.org readme and assets" → Run workflow, or `gh workflow run wporg-assets.yml`); the checks and what fails them; the `v`-stripping rule; a warning that the assets workflow publishes `main`'s readme immediately. LICENSE: MIT, 2026, Minuar.

Test the version check locally before pushing: run the "Check version numbers match" script body in a temp copy of `/Users/benne-air/projects/reusable-qr-codes-svn/trunk` with `GITHUB_REF_NAME=v1.1.0` (expect pass) and `GITHUB_REF_NAME=v9.9.9` (expect the mismatch error and exit 1). Also run `actionlint` if available (`brew list actionlint >/dev/null 2>&1 || brew install actionlint`), expecting no errors.

- [ ] **Step 5: Commit, push, tag v1**

```bash
git add .github/workflows/deploy.yml .github/workflows/assets.yml README.md LICENSE
git commit -m "feat: reusable workflows to publish WordPress plugins to WordPress.org"
git push -u origin main
git tag v1 && git push origin v1
```

---

### Task 2: Wire `reusable-qr-codes` to the workflows and dry-run it

**Files (repo `/Users/benne-air/projects/reusable-qr-codes-svn/trunk`):**
- Create: `.github/workflows/wporg-deploy.yml`, `.github/workflows/wporg-assets.yml`, `.wordpress-org/` (copies of the 12 PNGs in `/Users/benne-air/projects/reusable-qr-codes-svn/assets/`)
- Modify: `.distignore` (add `.wordpress-org` and `.wporg-dist`)

**Interfaces:**
- Consumes: `dev-minuar/wporg-release/.github/workflows/deploy.yml@v1`, `.../assets.yml@v1` (Task 1).

- [ ] **Step 1: Assets into git**

```bash
cd /Users/benne-air/projects/reusable-qr-codes-svn/trunk && mkdir -p .wordpress-org && cp ../assets/*.png .wordpress-org/ && ls .wordpress-org | wc -l   # expect 12
```

`.distignore`: add the lines `.wordpress-org` and `.wporg-dist` under the "Development files" group. Note: `.github` is already listed. `svn:ignore` on the SVN side is NOT changed in this plan (the local SVN copy is no longer the publishing path; record in the report that `.wordpress-org` would show as `?` there).

- [ ] **Step 2: Repository secrets**

```bash
printf '%s' minuar | gh secret set SVN_USERNAME --repo dev-minuar/reusable-qr-codes
security find-generic-password -a minuar -s '<https://plugins.svn.wordpress.org:443> Use your WordPress.org login' -w | gh secret set SVN_PASSWORD --repo dev-minuar/reusable-qr-codes
gh secret list --repo dev-minuar/reusable-qr-codes   # expect both names, no values shown
```

- [ ] **Step 3: Caller workflows**

`.github/workflows/wporg-deploy.yml`:

```yaml
name: WordPress.org deploy

on:
  push:
    tags:
      - 'v*'
  workflow_dispatch:
    inputs:
      version:
        description: 'Version to deploy (e.g. 1.1.0)'
        required: true
      dry-run:
        description: 'Dry run (no SVN commit)'
        type: boolean
        default: true

jobs:
  deploy:
    uses: dev-minuar/wporg-release/.github/workflows/deploy.yml@v1
    with:
      version: ${{ inputs.version || '' }}
      dry-run: ${{ github.event_name == 'workflow_dispatch' && inputs.dry-run || false }}
    secrets: inherit
```

`.github/workflows/wporg-assets.yml`:

```yaml
name: WordPress.org readme and assets

on:
  workflow_dispatch:

jobs:
  assets:
    uses: dev-minuar/wporg-release/.github/workflows/assets.yml@v1
    secrets: inherit
```

Note: `secrets: inherit` across repos works for callers in the same organization (both repos are in `dev-minuar`).

- [ ] **Step 4: Commit and push to `main` (no tag)**

```bash
git add .github/workflows/wporg-deploy.yml .github/workflows/wporg-assets.yml .wordpress-org .distignore
git commit -m "ci: publish to WordPress.org from GitHub Actions"
git push origin main
```

- [ ] **Step 5: Dry run**

```bash
gh workflow run wporg-deploy.yml --repo dev-minuar/reusable-qr-codes -f version=1.1.0 -f dry-run=true
sleep 5; RUN=$(gh run list --repo dev-minuar/reusable-qr-codes --workflow wporg-deploy.yml --limit 1 --json databaseId --jq '.[0].databaseId')
gh run watch "$RUN" --repo dev-minuar/reusable-qr-codes --exit-status
gh run view "$RUN" --repo dev-minuar/reusable-qr-codes --log > /tmp/wporg-dry-run.log
grep -n -E "Version mismatch|PHP files pass|Readme Validator|Notes:|Warnings|Fatal|Dry run|svn|Committing|Plugin Check" /tmp/wporg-dry-run.log | head -60
```

Expected: success; the version step prints `Tag: 1.1.0 | reusable-qr-codes.php Version: 1.1.0 | readme Stable tag: 1.1.0`; php lint passes; validator shows only Notes; Plugin Check passes (warnings ignored); the 10up step reports a dry run and no commit; the wait step is skipped. From the log, list the files the dry run would add/modify/delete in SVN trunk and confirm none of `.wordpress-org`, `docs`, `AGENTS.md`, `CLAUDE.md`, `.github`, `.wporg-dist` appear (Review Focus 3). With the code identical to the live 1.1.0, expect few or no trunk changes.

If Plugin Check fails on something real, report it — do not weaken the check. If it fails only because of the environment (e.g. needs a newer action version), report BLOCKED with the log lines.

- [ ] **Step 6: Report** — write the dry-run evidence (run URL, key log lines, the would-be SVN file list) to the report file given by the controller.
