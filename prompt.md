We have to update the ticket routing logic.
•	Old Logic
1.	When ticket is created, it will go to controller nodal of that division and department.
2.	Then controller nodal can either close it or forward to an controller of corresponding department or forward to another division or revert back to the customer for more information.
3.	Then controller can either close or forward back to the controller nodal for more information.
4.	Then controller nodal can revert the ticket for more information.
5.	Customer can submit additional information and then ticket will go to controller nodal again.
6.	If the ticket is closed by the controller, it will go the controller nodal approval.
7.	After the approval of controller nodal action taken will be seen to customer and the ticket will go to customer for feedback.
8.	If no feedback for 3 days, then it will close automatically.
9.	Same for controller nodal for closing ticket. He can also approve his own reply.
•	New logic 
o	We have to extend the old logic as follows:
1.	After closing of ticket by the controller it will go to the admin of that department for approval or edit & approval or reject (with rejection remarks.).
2.	Then after approval of admin of that department the ticket will go to Admin of ‘CML’ department of that division of zone. for approval or edit & approval or reject (with rejection remarks.)
3.	After approval of admin of cml it will go to the customer for feedback.
•	Admin can provide admin remarks to tickets up to three days even after closed status of ticket.
•	Admin remarks on tickets has to logged such as a report can be generated that how many time a department has got same admin remarks.
•	Or admin remarks for department can be seen together in remarks.
•	Controller nodal cannot approve or reject or edit reply by controller or reply by controller nodal (himself).
•	Action taken should be route through  admins.
