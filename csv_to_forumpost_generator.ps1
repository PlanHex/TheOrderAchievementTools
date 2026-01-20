# Configuration
$masterFile = "./forumdata/masterlist_generated.txt"
$rosterFile = "./forumdata/rosterlist_generated.txt"
$delimiter  = "|"

# Input Files
$csvCategories       = "./data/categories.csv"
$csvAchievements     = "./data/achievements.csv"
$csvUsers            = "./data/users.csv"
$csvUserAchievements = "./data/user_achievements.csv"

# Check if data exists
if (-not (Test-Path $csvCategories) -or -not (Test-Path $csvAchievements)) {
    Write-Error "Required CSV files not found in ./data/"
    exit
}

# ---------------------------------------------------------
# 1. Load Data
# ---------------------------------------------------------
Write-Host "Loading CSV Data..." -ForegroundColor Cyan

$categories       = Import-Csv $csvCategories       -Delimiter $delimiter -Encoding utf8
$achievements     = Import-Csv $csvAchievements     -Delimiter $delimiter -Encoding utf8
$users            = Import-Csv $csvUsers            -Delimiter $delimiter -Encoding utf8
$userAchievements = Import-Csv $csvUserAchievements -Delimiter $delimiter -Encoding utf8

# Create Lookups for efficiency
$achLookup = @{}
foreach ($ach in $achievements) {
    $achLookup[$ach.id] = $ach
}

# Group Achievements by Category ID
$achByCat = @{}
foreach ($ach in $achievements) {
    if (-not $achByCat.ContainsKey($ach.category_id)) {
        $achByCat[$ach.category_id] = @()
    }
    $achByCat[$ach.category_id] += $ach
}

# Group User Achievements by User ID
$userAchByUser = @{}
foreach ($ua in $userAchievements) {
    if (-not $userAchByUser.ContainsKey($ua.user_id)) {
        $userAchByUser[$ua.user_id] = @()
    }
    $userAchByUser[$ua.user_id] += $ua
}

# ---------------------------------------------------------
# 2. Generate Masterlist
# ---------------------------------------------------------
Write-Host "Generating Masterlist..." -ForegroundColor Cyan

$masterContent = [System.Text.StringBuilder]::new()

# Sort categories by display_order (ensure numerical sort)
$sortedCats = $categories | Sort-Object { [int]$_.display_order }

foreach ($cat in $sortedCats) {
    # Header: [B]Category Name[/B]
    [void]$masterContent.AppendLine("[B]$($cat.name)[/B]")
    [void]$masterContent.AppendLine("")

    if ($achByCat.ContainsKey($cat.id)) {
        # Sort achievements in this category
        $catAchs = $achByCat[$cat.id] | Sort-Object { [int]$_.display_order }

        foreach ($ach in $catAchs) {
            # Format: [IMG]url[/IMG][B]Title (Pts)[/B], Category - Description [code][IMG]url[/IMG][/code]
            
            # 1. Format Points
            $ptsStr = "($($ach.points)p)"
            
            # 2. Format Description with Category prefix
            # This ensures the generator script will strip "$catEscaped" correctly next time
            $descStr = ""
            if (-not [string]::IsNullOrWhiteSpace($ach.description)) {
                $descStr = ", $($cat.name) - $($ach.description)"
            }

            # 3. Build the line
            $line = "[IMG]$($ach.image_url)[/IMG][B]$($ach.title) $ptsStr[/B]$descStr[code][IMG]$($ach.image_url)[/IMG][/code]"
            
            [void]$masterContent.AppendLine($line)
            [void]$masterContent.AppendLine("") # Empty line between entries
        }
    }
    [void]$masterContent.AppendLine("") # Extra spacing between categories
}

# Save Masterlist
$masterContent.ToString() | Out-File $masterFile -Encoding utf8
Write-Host "Saved: $masterFile" -ForegroundColor Green

# ---------------------------------------------------------
# 3. Generate Rosterlist
# ---------------------------------------------------------
Write-Host "Generating Rosterlist..." -ForegroundColor Cyan

$rosterContent = [System.Text.StringBuilder]::new()

# Sort users by ID (preserves original parsing order)
$sortedUsers = $users | Sort-Object { [int]$_.id }

foreach ($u in $sortedUsers) {
    # Header: [B]UserName[/B]
    [void]$rosterContent.AppendLine("[B]$($u.name)[/B]")
    [void]$rosterContent.AppendLine("")

    if ($userAchByUser.ContainsKey($u.id)) {
        # Get user's achievements and sort
        $uAchs = $userAchByUser[$u.id] | Sort-Object { [int]$_.display_order }
        
        # Build the image string
        $imgList = @()
        foreach ($ua in $uAchs) {
            $achData = $achLookup[$ua.achievement_id]
            if ($achData) {
                # Reconstruct the title attribute: title="Name (Points)"
                # This matches the rosterlist source format
                $titleAttr = 'title="{0} ({1}p)"' -f $achData.title, $achData.points
                $imgTag = '[IMG {0}]{1}[/IMG]' -f $titleAttr, $achData.image_url
                $imgList += $imgTag
            }
        }
        
        # Join images with a space
        $renderString = $imgList -join " "

        # 1. Output the rendered images
        [void]$rosterContent.AppendLine($renderString)
        [void]$rosterContent.AppendLine("")

        # 2. Output the Code block
        [void]$rosterContent.AppendLine("Code:")
        [void]$rosterContent.AppendLine("[plain]$renderString[/plain]")
    }
    
    [void]$rosterContent.AppendLine("")
    [void]$rosterContent.AppendLine("") # Double space between users
}

# Save Rosterlist
$rosterContent.ToString() | Out-File $rosterFile -Encoding utf8
Write-Host "Saved: $rosterFile" -ForegroundColor Green

Write-Host "Done! Forum lists regenerated."