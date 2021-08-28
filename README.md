- composer install
- import sql file to create db
- php artisan migrate
- Add Postman collection URL: https://www.getpostman.com/collections/8fdeea36149a15c5f732
- Call the register api
- Call the login api to get the access token. (Could be done differently but had already done it using login)
- Test the subscribe / unsubscribe APIs

Note that I used JWT secret to get the encoded token (https://jwt.io/)
