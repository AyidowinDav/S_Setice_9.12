<#
PowerShell helper to update php.ini values for WAMP (run as Administrator)
Usage:
  .\update_php_ini.ps1                -> script tries to locate php.ini under C:\wamp64 and prompts
  .\update_php_ini.ps1 -IniPath 'C:\wamp64\bin\php\php8.2.0\php.ini'   -> use explicit path

This script will backup the original php.ini to php.ini.bak.TIMESTAMP and then set:
  upload_max_filesize = 100M
  post_max_size = 110M
  memory_limit = 256M
  max_execution_time = 300
  max_input_time = 300

It requires running PowerShell as Administrator to write into WAMP folders.
#>
param(
    [string]$IniPath
)

function Find-PHPIni {
    Write-Host "Recherche de php.ini sous C:\\wamp64 (peut prendre quelques secondes)..." -ForegroundColor Cyan
    $candidates = Get-ChildItem -Path 'C:\wamp64' -Filter 'php.ini' -Recurse -ErrorAction SilentlyContinue | Select-Object -Unique -Property FullName
    if ($candidates) { return $candidates[0].FullName }
    return $null
}

if (-not $IniPath) {
    $found = Find-PHPIni
    if ($found) {
        Write-Host "Fichier php.ini trouvé: $found" -ForegroundColor Green
        $answer = Read-Host "Utiliser ce fichier ? (O/n)"
        if ($answer -eq 'n' -or $answer -eq 'N') {
            $IniPath = Read-Host "Saisissez le chemin complet vers php.ini"
        } else {
            $IniPath = $found
        }
    } else {
        $IniPath = Read-Host "php.ini introuvable automatiquement. Indiquez le chemin complet vers php.ini"
    }
}

if (-not (Test-Path $IniPath)) {
    Write-Error "Le fichier spécifié n'existe pas: $IniPath"
    exit 1
}

# Ensure we can write (require admin)
try {
    $test = Get-Item $IniPath
} catch {
    Write-Error "Impossible d'accéder à $IniPath : $_"
    exit 1
}

$timestamp = Get-Date -Format 'yyyyMMdd_HHmmss'
$backup = "$IniPath.bak.$timestamp"
Copy-Item -Path $IniPath -Destination $backup -Force
Write-Host "Sauvegarde créée: $backup" -ForegroundColor Yellow

# Read content
$content = Get-Content -Path $IniPath -Raw -ErrorAction Stop

function Set-OrAdd ($name, $value) {
    $pattern = "(?mi)^\s*" + [regex]::Escape($name) + "\s*=.*$"
    if ($content -match $pattern) {
        $content = [regex]::Replace($content, $pattern, "$name = $value")
        Write-Host "Remplacé: $name = $value"
    } else {
        $content += "`r`n$name = $value"
        Write-Host "Ajouté: $name = $value"
    }
}

# Desired values
Set-OrAdd 'upload_max_filesize' '100M'
Set-OrAdd 'post_max_size' '110M'
Set-OrAdd 'memory_limit' '256M'
Set-OrAdd 'max_execution_time' '300'
Set-OrAdd 'max_input_time' '300'

# Write back
try {
    Set-Content -Path $IniPath -Value $content -Encoding UTF8 -Force
    Write-Host "php.ini mis à jour avec succès." -ForegroundColor Green
    Write-Host "Veuillez redémarrer WAMP (Apache) pour prendre en compte les changements."
} catch {
    Write-Error "Échec lors de l'écriture de php.ini : $_"
    Write-Host "La sauvegarde est disponible ici : $backup"
    exit 1
}

# Show changes summary (diff-like)
Write-Host "--- Aperçu rapide (lignes modifiées ou ajoutées) ---" -ForegroundColor Cyan
Get-Content -Path $backup -ErrorAction SilentlyContinue | Select-String -Pattern 'upload_max_filesize|post_max_size|memory_limit|max_execution_time|max_input_time' -SimpleMatch
Get-Content -Path $IniPath | Select-String -Pattern 'upload_max_filesize|post_max_size|memory_limit|max_execution_time|max_input_time' -SimpleMatch

Write-Host "Script terminé." -ForegroundColor Green
