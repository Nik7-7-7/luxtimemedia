## Development guide

- Open `XAMPP` and start the Apace and MySQL

![XAMPP](https://github.com/Agenty/dayschedule-wordpress/assets/6106479/8cad16ad-9c60-403e-a849-4a3f411c3d4f)

- Go to http://localhost/wordpress/wp-login.php
- Login with admin credentials 
- Go to [plugins](http://localhost/wordpress/wp-admin/plugins.php) and refresh
- Make changes in code and test

## Release guide
Watch video tutorial - 

[![Publish new version of Wordpress](https://img.youtube.com/vi/IhtiFwJwDIA/0.jpg)](https://www.youtube.com/watch?v=IhtiFwJwDIA)

### SVN Link
https://plugins.svn.wordpress.org/dayschedule-appointment-event-and-service-booking

### Public Link
https://wordpress.org/plugins/dayschedule-appointment-event-and-service-booking

### Trunk commit
1. Open the development directory
2. Increase the version in `readme.txt` and `dayschedule.php`
3. Copy all files into `/trunk` folder on SVN
4. Commit the trunk folder

### Tags commit
1. Create a new version folder under `/tags`
2. SVN commit to publish the changes

## SVN Credentials

SVN credentials are same as wordpress.org credentials for `dayschedule`
```
Username: dayschedule
Password: Get it from wordpress.org
```


