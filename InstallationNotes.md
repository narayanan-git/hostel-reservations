# Details #

  * Enable logging on PHP if desired (edit php.ini)
```
log_errors = On
error_log = php_errors.log
display_errors = On  (for development only)
```

  * Once wordpress has been installed and configured, copy the contents of the plugin into wp-content/plugins

  * login as wordpress admin user, under plugins menu, activate hostel backoffice plugin


To link to the pages so that non-admin users can see, we'll need to add some placeholders which will be replaced with the appropriate pages.

  * Under the Pages menu, click Add New Page. Enter "Add Booking" as the title. Click on "Change Permalinks". Select "Post name" as the default permalink format. If you require a different format on your site, you will need to correct the permalinks on the Settings page.

  * The content can be left blank. Visibility should remain public; no parent specified, and template can be left as "Default Template". Click publish. Now edit the permalink so it reads "edit-booking". Click update.

  * Now if you go back to the main site, there should be a new page "Add Booking" available from the menu bar. Clicking on it will bring up the Add Booking page.

  * Now enter a new page "Admin" with a permalink of "/admin" as before. This will be the parent page for the other admin pages.

  * The following pages (title => permalink) can be added with a _parent_ page of "Admin":
    * Daily Summary => /admin/summary
    * Allocations => /admin/allocations
    * Bookings => /admin/bookings
    * Resources => /admin/resources
> These permalinks can be modified under the plugin settings page.

These pages should now be visible on the main site accessible via the menu. You can maintain a structure of the menu items by creating a menu using the [WordPress admin options](http://codex.wordpress.org/WordPress_Menu_User_Guide).