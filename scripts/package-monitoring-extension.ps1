$extensionRoot = Join-Path $PSScriptRoot '..\extensions\lmg-monitoring'
$releaseRoot = Join-Path $PSScriptRoot '..\public\downloads'
$installPackagePath = Join-Path $releaseRoot 'lmg-privacy-monitoring-install.zip'
$storePackagePath = Join-Path $releaseRoot 'lmg-privacy-monitoring-chrome-web-store.zip'

if (-not (Test-Path -LiteralPath $extensionRoot)) {
    throw 'Folder extension LMG Privacy Monitoring tidak ditemukan.'
}

New-Item -ItemType Directory -Path $releaseRoot -Force | Out-Null

foreach ($packagePath in @($installPackagePath, $storePackagePath)) {
    if (Test-Path -LiteralPath $packagePath) {
        Remove-Item -LiteralPath $packagePath -Force
    }
}

# This package preserves the root folder. Users extract it, then choose the
# lmg-monitoring folder directly in Chrome's "Load unpacked" picker.
Compress-Archive -Path $extensionRoot -DestinationPath $installPackagePath

# Chrome Web Store requires manifest.json to be at the ZIP root.
$storeFiles = @(
    'manifest.json',
    'service-worker.js',
    'popup.html',
    'popup.css',
    'popup.js'
) | ForEach-Object { Join-Path $extensionRoot $_ }
Compress-Archive -LiteralPath $storeFiles -DestinationPath $storePackagePath

Write-Output $installPackagePath
Write-Output $storePackagePath
