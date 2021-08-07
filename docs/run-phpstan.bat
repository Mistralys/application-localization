@echo off

cls 

echo -------------------------------------------------------
echo RUNNING PHPSTAN ANALYSIS
echo -------------------------------------------------------

echo.

call ../vendor/bin/phpstan analyse -c ./config/phpstan.neon -l 7 > phpstan/output.txt

call "phpstan/output.txt"

echo.
echo Saved to phpstan/output.txt.
echo.

pause