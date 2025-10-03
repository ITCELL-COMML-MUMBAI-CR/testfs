Ticket routing logic bugs:
1.	Too many statuses for ticket reply approval.
2.	So keep awaiting_approval status by merging awaiting_admin_approval and awaiting cml admin approval.
3.	Controller_nodal must see the ticket in view mode when awaiting approval. He cannot take any action.
4.	Admin can be identified by the assign to department, Since if ticket has assigned to department as OPTG then admins of OPTG of that division and zone will able to approve action.
5.	After that assigned to department will be CML and status will remain as it is ‘Awaiting_approval’.
6.	You have to use same action buttons like we are using for the controller_nodal for approval of actions. Just shift them to admins only.
7.	Log the older action reply in transactions if edited by any of admins.
8.	Also check that all endpoints are working properly.
