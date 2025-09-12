We are creating SAMPARK (Support and Mediation Portal for All Rail Cargo), a website made for Freight Customers to effectively communicate their needs and bottlenecks to the system and for the administration to understand the actual root cause also the reason for the customers worries.
•	The Technology stack we will use:
1.	Frontend: - HTML, CSS, JavaScript
2.	Backend: - PHP, MySQL
3.	Architecture: - MVC architecture and API’s 
4.	Libraries: - Data table, Sweetalert2, Bootstrap 5, jQuery, Font Awesome Icons, Select 2, Mermaid etc.

•	The look and feel of system will be:
Please read design.md file to get idea of system frontend.

•	The system overview (logic):
User Roles & Hierarchy
1.	Customer - End users (freight customers) who log support tickets
2.	Controller - Department controllers within divisions
3.	Controller_Nodal - Commercial department controllers at zone/division level
4.	Admin - Administrative users with limited operational roles
5.	Superadmin - System administrators with no ticket workflow participation
Core Workflow Process
Initial Ticket Assignment
•	When a customer logs a support ticket, it is automatically assigned to the controller_nodal (Commercial Department Controller) in the relevant Zone and Division of the Terminal
•	The commercial controller serves as the primary nodal point for all tickets
Permission Matrix by Role
1. Forwarding Support Tickets
•	controller_nodal: Can forward tickets to: 
o	Nodal controllers of other divisions
o	Headquarters (HQ)
o	Controllers of different departments within same division
o	Can revert tickets back to customers for additional details
•	Controller: Can forward tickets to nodal controller of the same division only
•	Customer: Cannot forward tickets; can only resubmit with additional details when reverted
•	Admin/Superadmin: No forwarding permissions
2. Reply/Action Taken on Tickets
•	controller_nodal: Can reply to and take action on support tickets
•	Controller: Can reply to and take action on support tickets
•	Customer/Admin/Superadmin: Cannot reply or take action
3. Approval of Replies/Actions
•	controller_nodal: Can approve replies and actions taken on tickets
•	Controller/Customer/Admin/Superadmin: Cannot approve replies/actions
4. Rejection of Replies/Actions
•	controller_nodal: Can reject replies and actions taken on tickets
•	Controller/Customer/Admin/Superadmin: Cannot reject replies/actions
5. Internal Remarks System
•	controller_nodal: Can add internal remarks during forwarding/replying/approving/reverting/rejecting
•	Controller: Can add internal remarks while forwarding/replying tickets
•	Admin: Can add internal remarks on all transactions within their jurisdiction
•	Customer: Cannot add internal remarks and cannot view any internal remarks
•	Superadmin: No internal remarks permissions
Priority Management System
Priority Levels & Auto-Escalation
1.	Normal - Default priority when ticket is initially logged
2.	Medium - Automatically set after 3 hours with no updates
3.	High - Automatically set after 12 hours with no updates
4.	Critical - Automatically set after 24 hours with no updates
Priority Reset Conditions
Priority resets to "Normal" when:
•	Ticket is reverted to customer
•	Ticket is forwarded to another division
Priority Finalization
•	Priority escalation stops permanently once reply/action is approved by controller_nodal
Status Types
The system tracks tickets through these status states:
1.	Pending - Awaiting action
2.	Awaiting Feedback - Waiting for response from Customer
3.	Awaiting Info - Additional information required from Customer
4.	Awaiting Approval - Reply/action pending approval from Nodal Controller
5.	Closed - Ticket resolved and closed
Key Business Rules
1.	All tickets must initially flow through controller_nodal (Commercial Department)
2.	Only controller_nodal has approval/rejection authority for final resolution
3.	Internal remarks are never visible to customers
4.	Priority escalation is time-based and automatic
5.	Customer interaction is limited to initial submission and providing additional details when requested
6.	The system maintains strict hierarchical control with clear separation of duties
Technical Considerations for AI Implementation
•	Implement role-based access control (RBAC) strictly
•	Build automated priority escalation with timer-based triggers
•	Ensure internal remarks are completely segregated from customer-facing content
•	Design workflow state machine to handle all status transitions
•	Implement proper audit trails for all actions and state changes
•	Build notification system for priority escalations and status changes
•	Generate a function to create a complaint number format – “YYYYMMDD” + “four digit number starting with 0001 and up to 9999”.
•	Implement a system where there can be an Interim Reply that can be sent by the controller so that the Customer understands that the Support Ticket have been noted and Work is going on this should be an interim remark. Also after Action taken, an Officers Remarks can be Added for much Stricter Action by Officer. This is not a Mandatory Remark but officer can decide on adding the Remark in Cases he Feels the Action taken is not enough.

•	The system pages overview:
1. PUBLIC/GUEST PAGES
1.1 Home Page (Landing Page)
Features:
•	Header: Modern UI with dual railway logos on both sides
•	Navigation: Dynamic navbar showing Login when no session is active, role-specific navigation when logged in
•	Marquee: Scrolling banner below header displaying latest news and announcements
•	Main Content Cards: 
o	Latest News Card (dynamic, admin-managed)
o	Announcements Card (dynamic, admin-managed)
o	External Links Card (dynamic, admin-managed)
•	Primary CTA: "Raise a Support Ticket" card prominently displayed
•	Interactions: 
o	Long messages are clickable, opening detailed modals
o	All content dynamically managed by admin
o	Ticket raising redirects to login if no session, otherwise to ticket creation
1.2 Dual Login Page
Features:
•	Modern Design: Single page with animated radio button switcher
•	Customer Login Section: 
o	Email or Phone number (flexible input)
o	Password field
o	"Forgot Password" link
•	Admin/Staff Login Section: 
o	Login ID field
o	Password field
o	Role-based authentication
•	Smooth Animations: Transition between customer and admin login forms
•	Additional Links: Sign up for customers, help documentation
1.3 Customer Sign Up Page
Features:
•	Registration Form: 
o	Personal details (Name, Email, Phone)
o	Company/Business information
o	GSTIN details
o	Division selection (for approval routing)
o	Password creation with strength indicator
•	Approval Workflow: Registration request automatically routed to divisional admin for approval
•	Status Tracking: Email confirmation and status updates
•	Validation: Real-time form validation and error handling
________________________________________
2. CUSTOMER INTERFACE
2.1 Customer Dashboard
Features:
•	Welcome Section: Personalized greeting with quick stats
•	Quick Actions: Raise new ticket, view pending tickets
•	Announcements: Division-specific or general announcements
•	Help Resources: Quick access to guidelines and FAQs
2.2 My Support Tickets
Features:
•	Ticket Visibility: Only shows tickets that are NOT closed (active tickets only)
•	Tabular Display: 
o	Ticket ID, Subject, Status, Priority, Created Date, Last Updated
o	Color-coded priority indicators
•	Filtering Options: 
o	By status (Pending, Awaiting Feedback, Awaiting Info)
o	By priority (Normal, Medium, High, Critical)
o	Date range filter
•	Ticket Details Modal: 
o	Complete ticket information (customer-visible fields only)
o	Communication history
o	Current status and next expected action
•	Auto-Close Warning: Information about 3-day auto-close for reverted tickets
•	Feedback System: 
o	Review modal for replied tickets
o	Options: Unsatisfactory, Satisfactory, Excellent
o	Optional remarks (mandatory for Unsatisfactory)
2.3 Create New Support Ticket
Three-Card Layout:
Card 1: Customer Details
•	Read-only Display: Name, email, phone, company, GSTIN
•	Edit Profile Link: Quick access to profile management
Card 2: Ticket Form
Form Fields:
•	Issue Type: Dropdown from database
•	Issue Subtype: Filtered dropdown based on selected type
•	Reference Numbers: FNR/GSTIN/e-Indent (optional)
•	Division Filter: Dropdown to filter shed options
•	Shed Selection: Searchable dropdown (Code + Name + Division + Zone)
•	Wagon Type: Dropdown with options: 
o	Container Wagon, 
o	Covered Wagon, 
o	Flat Wagon
o	Hopper Wagon, 
o	Open Wagon, 
o	Tank Wagon, 
o	Well Wagon
•	Description: Text area (20-200 characters, with counter)
•	File Upload: 
o	Max 3 files, 2MB each
o	Formats: JPG, JPEG, PNG, GIF, PDF, DOC, DOCX
o	Drag-and-drop interface with preview
o	Create an own model that will compress the file while submission.
o	When uploading file to server keep its name with complaint ID followed by image1, image2….
Card 3: Guidelines
Before Submission:
•	Complete required fields checklist
•	Location details importance
•	Date/time inclusion
•	Document attachment tips
•	Wagon-specific issue guidance
After Submission:
•	Unique ticket ID generation
•	Tracking information
•	Response timeline (24-48 hours)
•	Update notifications
•	Follow-up capabilities
2.4 Help Page
Features:
•	System Flowchart: Visual representation of ticket lifecycle
•	User Manual: Step-by-step customer guide
•	FAQ Section: Common questions and answers
•	Contact Information: Support contact details
•	Video Tutorials: Embedded help videos
2.5 Customer Profile Page
Features:
•	Editable Fields: Name, phone, email, company details
•	Security Settings: Password change, security questions
•	Communication Preferences: Email/SMS notification settings
•	Account Status: Registration status, approval details
2.6 Privacy Policy Page
Features:
•	Data Collection: What information is collected
•	Usage Terms: How data is used and protected
•	Rights: User rights and data access
•	Contact: Privacy officer contact information
________________________________________
3. CONTROLLER INTERFACE
3.1 Controller Support Hub Home
Three-Tab Layout:
Tab 1: Filters
•	All Support Tickets: Complete ticket history (received + completed)
•	Assigned to Me: Tickets requiring controller action
•	Advanced Filters: 
o	Status, priority, date range
o	Issue type/subtype
o	Division/shed filters
o	Customer filters
Tab 2: Support Ticket List
•	Dynamic Table: Based on applied filters
•	Quick Info Display: 
o	Ticket ID, customer, subject, status, priority
o	Assignment information, last updated
o	Quick action buttons
•	Sorting/Pagination: Efficient data handling
•	Status Indicators: Visual priority and status markers
Tab 3: Support Ticket Details & Actions
•	Complete Ticket View: 
o	All ticket details and communication history
o	Internal remarks (not visible to customer)
o	Attachment viewing/download
•	Action Panel: 
o	Forward ticket (to nodal of same division)
o	Reply/Take action
o	Add internal remarks
o	Status updates
•	Communication Tools: Reply templates, quick responses
3.2 Reports Page
Features:
•	Redirect Integration: Links to 'reports.sampark.itcellbbcr.in'
•	Dashboard Widgets: Key metrics and KPIs
•	Export Options: PDF, Excel report generation
3.3 Help Page
Features:
•	Multi-Role Flowcharts: Customer, Controller, Admin workflows
•	User Manuals: Role-specific documentation
•	Training Materials: Best practices and procedures
3.4 Profile Page
Features:
•	User Details Management: Name, contact, department
•	Role Information: Current assignments and permissions
•	System Preferences: Interface customization
________________________________________
4. CONTROLLER_NODAL INTERFACE
4.1 Enhanced Support Hub
Additional Features Beyond Controller:
•	Cross-Division Forwarding: Send tickets to any division nodal/HQ
•	Interdepartmental Routing: Forward to different department controllers
•	Approval Workflows: Approve/reject replies and actions
•	Revert Capabilities: Send tickets back to customers for details
4.2 Advanced Management Dashboard
Features:
•	Multi-Division Overview: Tickets across jurisdiction
•	Priority Management: Critical ticket monitoring
•	Performance Metrics: Resolution times, satisfaction scores
•	Escalation Alerts: Automatic notifications for delayed tickets
________________________________________
5. ADMIN INTERFACE
5.1 Admin Dashboard
Features:
•	System Overview: User counts, ticket statistics
•	Recent Activities: System-wide activity monitoring
•	Quick Actions: User management, content updates
•	System Health: Performance metrics and alerts
5.2 User Management
Features:
•	Customer Management: 
o	Approve/reject registrations
o	User activation/deactivation
o	Profile editing and verification
o	Bulk user operations
•	Staff Management: 
o	Controller and nodal user creation
o	Role assignment and permissions
o	Department and division assignments
•	User Directory: Searchable user database with filters
5.3 Customer Management
Features:
•	Registration Approvals: Pending customer approvals by division
•	Customer Profiles: Complete customer information management
•	Account Status: Activation, suspension, deletion
•	Communication History: Customer interaction tracking
5.4 Category Management
Features:
•	Issue Types: Create, edit, delete issue categories
•	Issue Subtypes: Manage subcategories linked to main types
•	Category Hierarchy: Organize categories logically
•	Usage Analytics: Track category usage and effectiveness
5.5 Shed Management
Features:
•	Shed Directory: Complete database of railway sheds
•	Division Mapping: Shed-to-division relationships
•	Zone Management: Hierarchical organization
•	Search Optimization: Ensure efficient shed selection for customers
5.6 Content Management
Features:
•	News Management: Create, edit, schedule news items
•	Announcements: System-wide or division-specific announcements
•	External Links: Manage helpful external resources
•	Marquee Control: Manage scrolling banner content
5.7 Communication Management
Features:
•	Bulk Email System: Send emails to user groups
•	Email Templates: Pre-designed templates for common communications
•	Notification Settings: System notification configuration
•	SMS Integration: Bulk SMS capabilities
5.8 Email Template Management
Features:
•	Template Library: Pre-built email templates
•	Custom Templates: Create organization-specific templates
•	Variable System: Dynamic content insertion
•	Preview and Testing: Template testing before deployment
5.9 Reports & Analytics
Features:
•	Comprehensive Reports: User, ticket, performance reports
•	Dashboard Analytics: Visual data representation
•	Export Capabilities: Multiple format support
•	Scheduled Reports: Automated report generation and Email to Selected Users.
________________________________________
6. SUPERADMIN INTERFACE
6.1 System Administration
Features:
•	Complete System Access: All administrative functions
•	User Role Management: Assign and modify user permissions
•	System Configuration: Core system settings
•	Security Management: Access controls and audit logs
6.2 Advanced Analytics
Features:
•	System Performance: Server metrics and optimization
•	User Behaviour Analytics: Usage patterns and insights
•	Security Monitoring: Access logs and security alerts
•	Database Management: Query tools and optimization
________________________________________
7. TECHNICAL FEATURES
7.1 Responsive Design
•	Mobile-First: Optimized for all device sizes
•	Touch-Friendly: Intuitive mobile interactions
•	Progressive Web App: Offline capabilities where appropriate
7.2 Modern UI/UX
•	Material Design: Clean, modern interface
•	Accessibility: WCAG 2.1 AA compliance
•	Dark/Light Mode: User preference themes
•	Animations: Smooth transitions and micro-interactions
7.3 Security Features
•	Role-Based Access Control: Strict permission enforcement
•	Session Management: Secure authentication
•	Data Encryption: Sensitive data protection
•	Audit Trails: Complete action logging
7.4 Performance Optimization
•	Lazy Loading: Efficient data loading
•	Caching Strategies: Fast page loads
•	Database Optimization: Query performance
•	CDN Integration: Static asset delivery
7.5 Integration Capabilities
•	Email Services: SMTP integration for notifications
•	SMS Gateway: Mobile notifications
•	File Storage: Secure document management
•	External APIs: Railway data integration

	The File Structure overview:
Root Directories:
•	public/: This is the web root. It's the only directory that should be directly accessible from the internet. It contains the front-facing files of the application.
•	src/: This directory holds the core application logic, likely following an MVC (Model-View-Controller) pattern.
•	logs/: This directory is used for storing application and server logs.
public/ Directory Breakdown:
•	.htaccess: This is an Apache configuration file. It's likely used for URL rewriting (to create clean URLs), security rules, and other server configurations.
•	index.php: This is the main entry point of the application. All requests are likely routed through this file.
•	api/: This directory probably contains the application's API endpoints.
•	assets/: This holds static files:
o	css/: Stylesheets.
o	js/: JavaScript files.
o	images/: Image files.
o	fonts/: Font files.
•	libs/: This is likely for third-party libraries or frameworks.
•	pages/: This could contain individual pages or views of the application.
•	uploads/: This is for user-uploaded content, with a subdirectory for evidence.
src/ Directory Breakdown:
•	config/: For application configuration files (e.g., database credentials, API keys).
•	controllers/: Contains the controller files, which handle user input and interact with the models and views.
•	models/: Contains the model files, which represent the application's data and business logic.
•	utils/: For utility functions and helper classes.
•	views/: Contains the view files, which are responsible for the presentation layer (the UI).
o	modals/: A subdirectory specifically for modal dialogs.

	The Database overview:
1.	There will be tables for following:
a.	Customers
b.	Users
c.	Complaints
d.	Transactions
e.	Evidence
f.	Complaint_categories
g.	Email_templates
h.	Shed
i.	Wagon_details
j.	Quick_links
k.	News
2.	Customers table will have the columns such as:
a.	CustomerID – auto generate this from a script in php.
b.	Password
c.	Name
d.	Email
e.	Mobile
f.	Company Name
g.	Designation
h.	Role
i.	Status
j.	Created_at (date and time)
k.	Created_by (Users ID if done by any admin. Or when sign up then take approving users ID)
l.	Updated_at (date and time)
3.	Users table:
a.	Login_ID
b.	Password
c.	Role
d.	Department
e.	Division
f.	Zone
g.	Name
h.	Email
i.	Mobile
j.	Status
k.	Created_at (date and time)
l.	Created_by
m.	Updated_at (date and time)
4.	Complaints Table:
a.	Complaint_ID
b.	Category_ID
c.	Date
d.	Time
e.	Shed_ID
f.	Wagon_ID
g.	Rating – (Excellent, Satisfactory, Unsatisfactory)
h.	Rating_remarks
i.	Description
j.	Action_taken
k.	Status
l.	Department
m.	Division
n.	Zone
o.	Customer_ID
p.	FNR Number
q.	Assigned_to_DepartmentID
r.	Forwarded_Flag
s.	Priority
t.	SLA
u.	Created_at
v.	Updated_at
5.	Transactions Table:
a.	Transaction_ID
b.	Complaint_ID
c.	Remarks
d.	Transaction_type
e.	From_user
f.	To_user
g.	From_department
h.	To_department
i.	Created_by – ID of User or Customer
j.	Created_by_type – Basically user role who created
k.	Created_at – Date and Time
6.	Evidence Table:
a.	Id
b.	Complaint_ID
c.	Image_1
d.	Image_2
e.	Image_3
f.	Uploaded_at
7.	Complaint Categories Table:
a.	Category_ID
b.	Category
c.	Type
d.	Subtype
8.	Email_Templates:
a.	Create this table according to the need in the system.
9.	Shed Table:
a.	Shed_ID
b.	Division
c.	Zone
d.	Terminal
e.	Name
f.	Type
10.	Wagon Details Table
a.	Wagon_ID
b.	WagonCode
c.	Type
11.	Quick links and News Table:
a.	Create this table according to the need in the system.
12. Create a table to SLA definitions


1. Provided access to the division across the CR zone.
2. Created Reports portal to overview the generated support tickets.
3. Provided admin ID to divisions to register new customer.
4. Currently adding a feature for name of individual controller who took the action.
5. Admin will get option to provide remarks on internal remarks given by the controllers. Or Admin can send instructions on specific support ticket.

