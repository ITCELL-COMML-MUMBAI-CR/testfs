1. There are major flaws in notification system.
2. When SUPERADMIN creates a notification it creates a separate notification for every user. this will be critical when users will increase.
3. In @TestController.php there are methods which are not defined in corresponding model. And one sendnotification method it says "Method 'TestController::sendNotification()' is not compatible with method 'BaseController::sendNotification()'"
4.The alert with auto close comes for notification. It must not be auto close user will close it. And Provide the view ticket link in that alert.
5. View ticket link is giving access denied after clicking.
6. While sending test notification or email Superadmin must have following structured form.
a. Select zone or all zones
b.select division from selected zone or all zone.
c. select department type or all departments.
7. Notifications can not be user specific like tickets. Except for customer notification should be customer specific.