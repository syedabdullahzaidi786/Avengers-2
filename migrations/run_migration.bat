@echo off
C:\xampp\mysql\bin\mysql.exe -u root avengers < "c:\xampp\htdocs\Avengers\migrations\add_fee_types.sql"
echo Migration completed.
pause
