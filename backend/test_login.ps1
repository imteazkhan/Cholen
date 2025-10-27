# Test Login Flow
$phone = "01234567890"

Write-Host "1. Generating login code..." -ForegroundColor Green
Invoke-WebRequest -Uri "http://localhost:8000/api/login" -Method POST -ContentType "application/json" -Body "{`"phone`":`"$phone`"}"

Write-Host "`n2. Checking login code in database..." -ForegroundColor Green
$code = php artisan tinker --execute="echo App\Models\User::where('phone', '$phone')->first()->login_code;"

Write-Host "Login code: $code" -ForegroundColor Yellow

Write-Host "`n3. Verifying login code..." -ForegroundColor Green
$response = Invoke-WebRequest -Uri "http://localhost:8000/api/login/verify" -Method POST -ContentType "application/json" -Body "{`"phone`":`"$phone`",`"login_code`":`"$code`"}"

Write-Host "Response: $($response.Content)" -ForegroundColor Cyan

Write-Host "`n4. Checking if code was cleared..." -ForegroundColor Green
$clearedCode = php artisan tinker --execute="echo App\Models\User::where('phone', '$phone')->first()->login_code ?? 'NULL';"
Write-Host "Login code after verification: $clearedCode" -ForegroundColor Yellow