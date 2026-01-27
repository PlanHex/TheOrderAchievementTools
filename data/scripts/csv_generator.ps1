# Configuration
$masterFile = "./data/forumdata/masterlist.txt"
$rosterFile = "./data/forumdata/rosterlist.txt"
$delimiter  = "|"

# Output Files
$csvCategories = "./data/categories.csv"
$csvAchievements = "./data/achievements.csv"
$csvUsers = "./data/users.csv"
$csvUserAchievements = "./data/user_achievements.csv"

# Data Storage
$categories = @()
$achievements = @()
$users = @()
$userAchievements = @()

$fileNameToIdMap = @{}
$catId = 1
$achId = 1
$userId = 1

if (Test-Path $masterFile) {
    Write-Host "Processing Masterlist..." -ForegroundColor Cyan
    # Explicitly use UTF8 to handle special characters correctly
    $masterContent = Get-Content $masterFile -Raw -Encoding utf8
    
    # Split by blocks starting with [B] (Category) or [IMG] (Achievement)
    $blocks = $masterContent -split "(?m)^(?=\[B\]|\[IMG\])" | Where-Object { $_.Trim() -ne "" }

    $currentCatId = 0
    $currentCatName = ""
    $catOrder = 1
    $achOrder = 1

    foreach ($block in $blocks) {
        $block = $block.Trim()

        # Match Category: [B]Category Name[/B]
        if ($block -match "^\[B\](?<name>[^\[\r\n]+)\[/B\]$") {
            $currentCatName = $Matches['name'].Trim()
            $currentCatId = $catId++
            $categories += [PSCustomObject]@{
                id            = $currentCatId
                name          = $currentCatName
                display_order = $catOrder++
            }
            $achOrder = 1
        }
        # Match Achievement: Handles [IMG]...[/IMG] [B]Title (Pts)[/B] Desc [code]
        elseif ($block -match "(?s)\[IMG\](?<url>.*?)\[/IMG\]\s*\[B\](?<title>.*?)(?:\s*\((?<pts>[-+]?\d+)p\))?\[/B\](?<desc>.*)") {
            $url = $Matches['url'].Trim()
            $fileName = Split-Path $url -Leaf # Extract filename for comparison (e.g., image.png)
            $title = $Matches['title'].Trim()
            $points = if ($Matches['pts']) { [int]$Matches['pts'] } else { 0 }
            
            # Check for duplicate filenames in Masterlist
            if ($fileNameToIdMap.ContainsKey($fileName)) {
                Write-Warning "Duplicate achievement image filename found: '$fileName' (Title: $title). Previous ID: $($fileNameToIdMap[$fileName])"
            }

            # Isolate Description: Take text before the first [code] tag
            $rawDesc = $Matches['desc']
            if ($rawDesc -match "(?s)(.*?)\[code\]") { $rawDesc = $Matches[1] }

            # Clean Description: Remove category prefix (e.g., ", Minor Personal - ")
            $catEscaped = [regex]::Escape($currentCatName)
            $cleanDesc = ($rawDesc -replace "^[\s,\-\.]*$catEscaped[\s\-\.]*", "").Trim(" -,`t`n`r")
            $thisAchId = $achId++
            $fileNameToIdMap[$fileName] = $thisAchId # Store by filename

            $achievements += [PSCustomObject]@{
                id            = $thisAchId
                category_id   = $currentCatId
                title         = $title
                description   = $cleanDesc
                points        = $points
                image_url     = $url
                display_order = $achOrder++
            }
        }
    }
    Write-Host "Success: Found $($achievements.Count) achievements." -ForegroundColor Green
}

if (Test-Path $rosterFile) {
    Write-Host "Processing Rosterlist..." -ForegroundColor Cyan
    $rosterContent = Get-Content $rosterFile -Raw -Encoding utf8
    $userSections = $rosterContent -split "(?m)^(?=\[B\])" | Where-Object { $_.Trim() -ne "" }

    foreach ($section in $userSections) {
        if ($section -match "^\[B\](?<userName>[^\[]+)\[/B\]") {
            $userName = $Matches['userName'].Trim()
            $thisUserId = $userId++
            $users += [PSCustomObject]@{ id = $thisUserId; name = $userName }
            $displayArea = [regex]::Split($section, "Code:", "IgnoreCase")[0]
            $imgMatches = [regex]::Matches($displayArea, '\[IMG[^\]]*\](?<url>.*?)\[/IMG\]')
            
            $uAchOrder = 1
            foreach ($m in $imgMatches) {
                $imgUrl = $m.Groups['url'].Value.Trim()
                $imgFileName = Split-Path $imgUrl -Leaf # Filename matching

                if ($fileNameToIdMap.ContainsKey($imgFileName)) {
                    $userAchievements += [PSCustomObject]@{
                        user_id        = $thisUserId
                        achievement_id = $fileNameToIdMap[$imgFileName]
                        display_order  = $uAchOrder++
                    }
                }
                else {
                    Write-Warning "User '$userName' has an achievement image not found in master list: $imgFileName"
                }
            }
        }
    }
}

# Export CSVs...
Write-Host "Exporting CSVs..." -ForegroundColor Green
$categories       | Export-Csv $csvCategories       -Delimiter $delimiter -NoTypeInformation -Encoding utf8
$achievements     | Export-Csv $csvAchievements     -Delimiter $delimiter -NoTypeInformation -Encoding utf8
$users            | Export-Csv $csvUsers            -Delimiter $delimiter -NoTypeInformation -Encoding utf8
$userAchievements | Export-Csv $csvUserAchievements -Delimiter $delimiter -NoTypeInformation -Encoding utf8

Write-Host "Done!" -ForegroundColor Cyan