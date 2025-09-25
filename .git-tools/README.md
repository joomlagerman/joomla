# .git-tools

This directory contains helper scripts for repository maintainers and contributors.  
These scripts are **not** part of the production codebase or release packages.

## Prerequisite: Upstream remote

These scripts require a remote named `upstream` that points to the original repository.  
You can check your current remotes with:

```
git remote -v
```

If `upstream` is not set, add it using:

```
# HTTPS (recommended)
git remote add upstream https://github.com/joomlagerman/joomla.git

# or via SSH
git remote add upstream git@github.com:joomlagerman/joomla.git
```

Make sure that `origin` remains your fork (your personal copy), and `upstream` points to the main/original project repository.

## Available scripts
- `sync-branch.sh` – syncs a given branch passed as parameter.
- `branches.txt` – defines which branches are considered for bulk sync.
- `sync-all.sh` – reads from `branches.txt` and syncs all listed branches.

## Usage
From the repo root, run:

```bash
# Sync a single branch (e.g. 6.0-dev)
./.git-tools/sync-branch.sh 6.0-dev

# Sync all branches listed in branches.txt
./.git-tools/sync-all.sh
```

**Note:** The older branch-specific scripts (`sync-5.3-dev.sh`, `sync-5.4-dev.sh`, `sync-6.0-dev.sh`) are deprecated and should be removed in favor of the parameterized scripts.

## Advanced usage with flags

You can control the sync behavior with these options:
- `--merge`  : allow a merge commit if histories diverged
- `--rebase` : rebase your local branch onto upstream
- `--force`  : skip the "working tree clean" check

Examples:

```bash
# Merge instead of fast-forward only
./.git-tools/sync-branch.sh 5.3-dev --merge

# Rebase instead of merge
./.git-tools/sync-branch.sh 5.4-dev --rebase

# Force run even with a dirty working tree
./.git-tools/sync-branch.sh 6.0-dev --force

# Combine flags for all branches
./.git-tools/sync-all.sh --merge --force
```

## Automation (Linux/macOS via cron)

You can run the sync automatically at intervals using `cron`.

### Example: run every night at 02:30
Edit your crontab with `crontab -e` and add:

```cron
30 2 * * * cd /path/to/your/repo && ./.git-tools/sync-all.sh --merge >> /path/to/your/repo/sync.log 2>&1
```

This will:
- change into your repo,
- run the sync (`--merge` allows a merge commit if necessary),
- append all output to `sync.log`.

**Notes**
- Make sure the scripts are executable: `chmod +x .git-tools/*.sh`
- If your repo needs a specific PATH (homebrew, custom git), prepend it:
  ```cron
  30 2 * * * PATH=/usr/local/bin:/opt/homebrew/bin:/usr/bin:/bin cd /path/to/your/repo && ./.git-tools/sync-all.sh --merge >> /path/to/your/repo/sync.log 2>&1
  ```
- macOS: `cron` works, but for tighter integration you can also use `launchd` (optional).

---

## Automation (Windows via Task Scheduler)

There are two common ways to run the Bash scripts on Windows.

### Option A: Git Bash

1) Create a small wrapper `run-sync-all.cmd` (for example in `C:\tools\run-sync-all.cmd`):

```bat
@echo off
"C:\Program Files\Git\bin\bash.exe" -lc "cd /c/path/to/your/repo && ./.git-tools/sync-all.sh --merge >> /c/path/to/your/repo/sync.log 2>&1"
```

> Replace `/c/path/to/your/repo` with your repo path in **POSIX style** (e.g. `C:\Users\you\repo` → `/c/Users/you/repo`).

2) Create a scheduled task (runs daily at 02:30):

```powershell
schtasks /Create /SC DAILY /ST 02:30 /TN "Repo Sync All" /TR "C:\tools\run-sync-all.cmd" /F
```

You can trigger it manually to test:

```powershell
schtasks /Run /TN "Repo Sync All"
```

### Option B: WSL (Windows Subsystem for Linux)

If your repo lives inside WSL or you prefer WSL’s environment, create `run-sync-all.ps1`:

```powershell
wsl bash -lc 'cd /path/to/your/repo && ./.git-tools/sync-all.sh --merge >> /path/to/your/repo/sync.log 2>&1'
```

Then schedule it:

```powershell
$Action = New-ScheduledTaskAction -Execute "powershell.exe" -Argument "-NoProfile -ExecutionPolicy Bypass -File `"C:\tools\run-sync-all.ps1`""
$Trigger = New-ScheduledTaskTrigger -Daily -At 02:30
Register-ScheduledTask -TaskName "Repo Sync All (WSL)" -Action $Action -Trigger $Trigger -Force
```

**Notes**
- Ensure Git is installed (Git Bash) or WSL is set up.
- For Git Bash, verify the path to `bash.exe` (commonly `C:\Program Files\Git\bin\bash.exe`).
- Logs are appended to `sync.log` in your repo—use them for troubleshooting.
