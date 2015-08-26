RESTFUL-API-in-native-PHP5
==========================





Step 1) all the request should be lokks like:

http:// your website url/api/v1/{RESOURCE eg: user}?action=GET& {your query string}&key=sdfsd7788sdf9df89


Step 2) Modify .htaccess file as per your local settings or server settings


Step 3) After modification of .htaccess file , all request will go to index.php


Step 4) resource.class.php---> request to the type of resources


Step 5) class Folder--> all the classes , depends on different type of resources



Folder Structure:-


-index.php

-.htaccess

-----------class/ contains all the class files, depends on the Type of resource like User etc.

-----------config/ configuration files

-----------include/ all the include files and folders on index.php
