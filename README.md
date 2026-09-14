## DAP - Electronic Daily Time Record

Electronic Daily Time Record (eDTR) is a web application to record the attendance of DAP Employees.

-   Records Time in/out
-   Added event recording that reflects in the DTR Report
-   System generated DTR Report with PDF preview and ready to print or download
-   Multiple ways to time entry. (Onsite, Work from Home, Location based)
-   Multi platform with mobile application for scanning the QR Code and obtaining location using GPS and Azure Maps to translate the coordinates to physical address

## Steps to clone this repository to your local development environment

-   Git clone this repository
-   Run **composer install**
-   Duplicate **.env.example** and rename it to **.env**
-   Run **php artisan key:generate**
-   Create your development database and configure its connection in **.env**
-   Run **php artisan migrate --seed** to migrate database tables and create the initial user
-   Run **npm install**
-   Run **php artisan serve** (Note: You can setup your virtual host in your development environment with a host of dtr-dev.test, if you have chosen another host name, update the APP_URL host in the **env**)
-   Run **npm run dev**
