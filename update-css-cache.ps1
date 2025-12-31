$files = @(
    "add-product.php",
    "addnewmentor.php",
    "addnewroom.php",
    "adminpanel.php",
    "availablerooms.php",
    "browsementors.php",
    "chat.php",
    "contactmentor.php",
    "lostandfound.php",
    "mentorpanel.php",
    "mybargains.php",
    "mydeals.php",
    "mymentors.php",
    "myselllist.php",
    "parttimejob.php",
    "postjob.php",
    "settings.php",
    "viewmentorprofile.php",
    "useraccount.php",
    "uius upplementhomepage.php",
    "shuttle_tracking_system.php",
    "shuttle_service.php",
    "SellAndExchange.php",
    "rentedrooms.php",
    "myjobs.php"
)

$oldPattern = 'assets/css/responsive-mobile.css"'
$newPattern = 'assets/css/responsive-mobile.css?v=2.0"'

foreach ($file in $files) {
    $path = "C:\xampp\htdocs\UIU_Supplements_Live\$file"
    if (Test-Path $path) {
        $content = Get-Content $path -Raw
        $content = $content -replace [regex]::Escape($oldPattern), $newPattern
        Set-Content $path -Value $content -NoNewline
        Write-Host "Updated: $file"
    }
}

Write-Host "`nAll files updated with cache busting parameter v=2.0"
