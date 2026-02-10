# SIMANSA V3 - Update Script (Windows / PowerShell)
# Usage (from repo root):
#   powershell -ExecutionPolicy Bypass -File .\update-simansa.ps1
#
# What it does:
# - Ensures working tree is clean
# - Fetch + switch to master + pull latest
# - Runs composer install (if available)
# - Clears Laravel caches

[CmdletBinding()]
param(
    [string]$Branch = "master",
    [switch]$SkipComposer,
    [switch]$SkipCacheClear
)

$ErrorActionPreference = "Stop"

function Write-Info([string]$Message) { Write-Host "[INFO]  $Message" -ForegroundColor Cyan }
function Write-Ok([string]$Message) { Write-Host "[OK]    $Message" -ForegroundColor Green }
function Write-Warn([string]$Message) { Write-Host "[WARN]  $Message" -ForegroundColor Yellow }
function Write-Err([string]$Message) { Write-Host "[ERROR] $Message" -ForegroundColor Red }

Push-Location $PSScriptRoot

try {
    Write-Info "Checking git status..."
    $porcelain = (git status --porcelain)
    if ($porcelain) {
        Write-Err "Working tree is not clean. Commit/stash changes before running update."
        Write-Host $porcelain
        exit 1
    }
    Write-Ok "Working tree clean"

    Write-Info "Fetching from origin..."
    git fetch origin --prune | Out-Host

    Write-Info "Switching to branch '$Branch'..."
    git switch $Branch | Out-Host

    Write-Info "Pulling latest from origin/$Branch..."
    git pull origin $Branch --ff-only | Out-Host
    Write-Ok "Git update complete"

    if (-not $SkipComposer) {
        if (Get-Command composer -ErrorAction SilentlyContinue) {
            if (Test-Path "composer.lock") {
                Write-Info "Running composer install..."
                composer install --no-interaction | Out-Host
                Write-Ok "Composer install done"
            } else {
                Write-Warn "composer.lock not found; skipping composer install"
            }
        } else {
            Write-Warn "Composer not found in PATH; skipping composer install"
        }
    } else {
        Write-Warn "SkipComposer enabled; skipping composer install"
    }

    if (-not $SkipCacheClear) {
        if (Test-Path "artisan") {
            Write-Info "Clearing Laravel caches (optimize:clear)..."
            php artisan optimize:clear | Out-Host
            Write-Ok "Cache clear complete"
        } else {
            Write-Warn "artisan not found; skipping cache clear"
        }
    } else {
        Write-Warn "SkipCacheClear enabled; skipping cache clear"
    }

    Write-Ok "Update finished"
}
catch {
    Write-Err $_.Exception.Message
    exit 1
}
finally {
    Pop-Location
}
