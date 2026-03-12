$files = Get-ChildItem -Path resources/js/components -Filter *.vue -Recurse
foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    $modified = $false

    if ($content -like "*'/api/*" -or $content -like "*'/images/*" -or $content -like "*'/storage/*" -or $content -like "*`"/images/*" -or $content -like "*`"/storage/*") {
        Write-Host "Fixing paths in: $($file.FullName)"
        $content = $content -replace "'/api/", "'api/"
        $content = $content -replace "'/images/", "Laravel.assetUrl + 'images/"
        $content = $content -replace "'/storage/", "Laravel.assetUrl + 'storage/"
        $content = $content -replace "`"/images/", "Laravel.assetUrl + 'images/"
        $content = $content -replace "`"/storage/", "Laravel.assetUrl + 'storage/"
        $modified = $true
    }

    if ($content -like "*Laravel.assetUrl*") {
        if ($content -notlike "*const Laravel = window.Laravel*") {
            Write-Host "Injecting Laravel variable in: $($file.FullName)"
            $content = $content -replace "<script setup>", "<script setup>`nconst Laravel = window.Laravel;"
            $modified = $true
        }
    }

    if ($modified) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
    }
}
