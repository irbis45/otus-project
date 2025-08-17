# Laravel
sail artisan migrate:fresh --env=testing
XDEBUG_MODE=coverage sail test --coverage-html coverage-report

# Documentation
sail artisan l5-swagger:generate
URL: /api/documentation/

# Laravel ide helper
cmd+shift-. every time after migration or model change

# Passport create token
sail artisan passport:client --personal
