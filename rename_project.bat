@echo off
REM Rename ai_ecommerce folder to snaptogift
REM Run this batch file to rename the project folder

echo Renaming ai_ecommerce to snaptogift...
cd /d C:\xampp\htdocs
if exist snaptogift (
    echo ERROR: snaptogift folder already exists!
    pause
    exit /b 1
)
rename ai_ecommerce snaptogift
echo Done! Project renamed to snaptogift
echo New URL: http://localhost/snaptogift/
pause
