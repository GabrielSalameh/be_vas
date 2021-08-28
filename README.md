- composer install
- import databaseCreation.sql file to create the db
- php artisan migrate
- Add Postman collection URL: https://www.getpostman.com/collections/8fdeea36149a15c5f732
- Call the register api
- Call the login api to get the access token. (Could be done differently but had already done it using login)
- Test the subscribe / unsubscribe APIs

Note that I used https://jwt.io/ website in order to add the needed info for the subscribe & unsubscribe APIs using the JWT secret from the env file.
