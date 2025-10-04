1.	Enhance the notification system.
2.	Currently it is generating some unnecessary notification like ‘ticket_created_notification’ and no information with it.
3.	Also, there are some notifications it generating that are good.
4.	Make Seamless all the notifications including those which are appearing on login of user/ customer.
5.	Make notifications minimal but informative.
6.	And provide the ticket view link with ticket number.
7.	In some cases (majorly when controller try) the link is not working and saying access denied.
8.	On Screen notifications are getting on screen. Check for every user role such as Customer, Controller, Controller_nodal and admin.
9.	Every activity on ticket under that users RBAC should show the notification to the users.
10.	For customer notification of ticket generated, Awaiting feedback and Awaiting Info only these to be shown.
11.	Customer must not see system update notification which shows 2 tickets priority escalated or such notification.
12.	Integrate the scattered methods and function and classes of notification to create a single system which will generate notifications for every purpose.
13.	Notification will be seen by three types 
a.	On Screen toast alert – Use sweetalert 2 for this
b.	In notification bell icon 
c.	On login Those notifications when user was unavailable only I those are not read yet.
14.	The notifications will be department specific not the user specific. Any user who will read the notification log the entry that which user has read the notification in that department.
15.	After any user that had read the notification in department then not need to show it to others in that department.
16.	Refresh for notifications should be 1 min (Only for notifications).
17.	So that the notifications will arrive near to real time.
18. Take reference from the database schema before starting any edits. Go for mysql directly to understand the database instead of files.