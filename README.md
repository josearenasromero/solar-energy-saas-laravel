IMPORTANT

Run these commands to start the database and passport:

php artisan db:wipe;php artisan migrate --seed;php artisan passport:install --force

Also, to run the server use:

php artisan serve

To run the task scheduler locally:

php artisan schedule:work