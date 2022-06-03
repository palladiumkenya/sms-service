# SMS Blacklist Gateway



Installation


Clone the repository

git clone https://github.com/palladiumkenya/sms-service
Switch to the repo folder cd sms-service

Install all the dependencies using composer

composer install
Copy the example env file and make the required configuration changes in the .env file

cp .env.example .env
Start the local development server


php -S localhost:8000 -t public

You can now access the server at http://localhost:8000


Environment variables
.env - Environment variables can be set in this file
Note : You can quickly set the database information and other variables in this file and have the application fully working.


