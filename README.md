# MGOD Learning Management System (LMS)

## What is MGOD-LMS?

MGOD-LMS is a complete Learning Management System built for schools, colleges, and training centers. It helps teachers and students work together in an organized way. The system makes it easy to create courses, give assignments, track grades, and communicate with students.

This system is free to use and can be set up at any educational institution.

---

## Who Can Use This System?

### For Administrators
You can manage the entire school system including:
- Adding and removing users (students, teachers, and staff)
- Creating departments and programs
- Setting up academic terms and schedules
- Managing course offerings and enrollments
- Viewing reports and system logs

### For Teachers (Instructors)
You can teach and manage your courses:
- Create and organize course materials
- Post assignments and set deadlines
- Grade student work through the gradebook
- Track student progress and attendance
- Send notifications to your students
- Schedule classes and view your teaching calendar

### For Students
You can learn and complete your coursework:
- View all your enrolled courses
- Access course materials and lectures
- Submit assignments before the deadline
- Check your grades and progress
- Receive notifications from teachers
- View your class schedule

---

## Main Features

### 1. User Management
- Easy registration and login system
- Different access levels for administrators, teachers, and students
- User profiles with personal information

### 2. Course Management
- Create courses with detailed information
- Add course prerequisites (courses students must complete first)
- Assign multiple instructors to one course
- Organize courses by department and program

### 3. Academic Structure
- Set up departments (e.g., Computer Science, Mathematics)
- Create programs (e.g., Bachelor of Science in IT)
- Define academic terms (e.g., Fall 2026, Spring 2027)
- Manage course offerings for each term

### 4. Assignment System
- Create different types of assignments
- Set submission deadlines
- Allow students to submit their work online
- Track who submitted and who did not

### 5. Grading System
- Complete gradebook for each course
- Create grade components (quizzes, exams, projects)
- Set up grading periods (midterm, final)
- Calculate final grades automatically
- Generate grade reports

### 6. Course Materials
- Upload and share learning materials
- Organize files by topic or week
- Students can download materials anytime

### 7. Enrollment Management
- Enroll students in courses
- Drop students from courses
- View enrollment lists
- Check course capacity

### 8. Schedule Management
- Create class schedules with time and location
- View weekly schedules
- Avoid schedule conflicts

### 9. Notifications
- Send messages to students
- Receive system notifications
- Stay updated on important announcements

### 10. Responsive Design
- Works on computers, tablets, and mobile phones
- Easy to use on any device
- Clean and simple interface

---

## System Requirements

Before you install MGOD-LMS, make sure your server has:

- **Web Server:** Apache (recommended) or Nginx
- **PHP Version:** PHP 8.1 or higher
- **Database:** MySQL 5.7 or higher / MariaDB
- **PHP Extensions Required:**
  - intl (International extension)
  - mbstring (Multibyte string extension)
  - mysqli (MySQL extension)
  - curl (for web requests)
  - gd or imagick (for image handling)
  - fileinfo (for file type detection)

---

## Installation Guide

Follow these steps to install MGOD-LMS at your institution:

### Step 1: Download the System

Download or clone this repository to your web server:

```
git clone https://github.com/MarjovicDEV/LMS-.git
```

Or download the ZIP file and extract it to your web folder.

### Step 2: Set Up the Database

1. Open your MySQL/MariaDB database manager (phpMyAdmin or command line)
2. Create a new database for the LMS:
   ```sql
   CREATE DATABASE mgod_lms;
   ```
3. Remember the database name - you will need it later

### Step 3: Configure the System

1. Find the file called `env` in the main folder
2. Rename it to `.env`
3. Open the `.env` file in a text editor
4. Fill in your database information:

```
database.default.hostname = localhost
database.default.database = mgod_lms
database.default.username = your_database_username
database.default.password = your_database_password
database.default.DBDriver = MySQLi
```

5. Set your base URL (your website address):
```
app.baseURL = 'http://your-school-website.com/'
```

6. Change the environment to production when ready:
```
CI_ENVIRONMENT = production
```

### Step 4: Set Folder Permissions

Make sure the `writable` folder can be written to by the web server:

**On Linux/Mac:**
```
chmod -R 755 writable/
```

**On Windows:**
Right-click the `writable` folder → Properties → Security → Make sure the web server user has write permission.

### Step 5: Run Database Migrations

Open your command line or terminal in the project folder and run:

```
php spark migrate
```

This will create all the necessary database tables.

### Step 6: Create the First Administrator Account

Run this command to create a seeder or manually insert the first admin user in the database:

```
php spark db:seed UserSeeder
```

Or create an admin account manually through the database.

### Step 7: Access the System

Open your web browser and go to:
```
http://your-school-website.com/
```

Log in with your administrator account and start setting up your system!

---

## How to Use the System

### For Administrators

#### First-Time Setup:
1. **Log in** with your administrator account
2. **Add Departments** - Go to Departments section and create your school departments
3. **Add Programs** - Create educational programs under each department
4. **Create Academic Terms** - Set up semesters or quarters
5. **Add Users** - Create accounts for teachers and students
6. **Create Courses** - Add all the courses your school offers
7. **Set Course Offerings** - Decide which courses are available each term
8. **Enroll Students** - Assign students to their courses

#### Managing Users:
- Go to **User Management** section
- Click **Add New User** button
- Fill in the user information (name, email, password)
- Select the user role (Admin, Teacher, or Student)
- Click **Save**

#### Managing Courses:
- Go to **Course Management** section
- Click **Add New Course** button
- Enter course code, name, description, and units
- Select the department
- Add prerequisites if needed
- Click **Save**

### For Teachers

#### Setting Up Your Course:
1. **Log in** to your account
2. **Go to My Courses** to see courses you teach
3. **Click on a course** to open it
4. **Add Materials** - Upload lecture notes, PDFs, presentations
5. **Create Assignments** - Set up homework with deadlines
6. **Set Up Gradebook** - Define grade components and grading periods

#### Creating an Assignment:
1. Open your course
2. Go to **Assignments** section
3. Click **Create New Assignment**
4. Enter the assignment title and instructions
5. Set the due date and time
6. Choose the assignment type
7. Set the maximum points
8. Click **Save**

#### Grading Students:
1. Open your course
2. Go to **Gradebook** section
3. Click on a student's name
4. Enter grades for each component
5. The system will calculate the final grade automatically
6. Click **Save**

### For Students

#### Viewing Your Courses:
1. **Log in** to your account
2. **Go to My Courses** - You will see all courses you are enrolled in
3. **Click on a course** to view its content

#### Submitting an Assignment:
1. Open your course
2. Go to **Assignments** section
3. Click on the assignment you want to submit
4. Click **Submit Assignment** button
5. Upload your file or enter your answer
6. Click **Submit**
7. You will see a confirmation message

#### Checking Your Grades:
1. Open your course
2. Go to **Gradebook** or **My Grades** section
3. You will see your scores for all graded work
4. View your current grade in the course

---

## Important Notes

### For Administrators:
- Always keep a backup of your database
- Change the default admin password immediately after installation
- Review system logs regularly to check for issues
- Keep the system updated with the latest version

### For Teachers:
- Set clear deadlines for assignments
- Update your gradebook regularly so students can track progress
- Use notifications to remind students about important dates
- Keep your course materials organized

### For Students:
- Check the system daily for new announcements
- Submit assignments before the deadline
- Contact your teacher if you have problems with submission
- Keep your password secure and do not share it

---

## Troubleshooting

### Cannot Log In
- Check that your username and password are correct
- Make sure CAPS LOCK is off
- Contact your administrator if you forgot your password

### Cannot Submit Assignment
- Check that you are submitting before the deadline
- Make sure your file is not too large (check file size limit)
- Try using a different web browser
- Contact your teacher for help

### Page Not Loading
- Check your internet connection
- Clear your browser cache and cookies
- Try refreshing the page
- Contact your administrator if the problem continues

### Cannot See My Courses
- Make sure you are enrolled in the courses
- Check with your administrator to verify your enrollment
- Try logging out and logging back in

---

## Technical Information

### Built With:
- **Framework:** CodeIgniter 4 (PHP Framework)
- **Database:** MySQL/MariaDB
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5
- **PDF Generation:** TCPDF Library

### File Structure:
- `app/` - Application code (controllers, models, views)
- `public/` - Public files (CSS, JavaScript, images)
- `writable/` - System logs and cache files
- `system/` - CodeIgniter framework files
- `vendor/` - Third-party libraries

---

## Security

### Keeping Your System Secure:
- Use strong passwords for all accounts
- Change default passwords immediately
- Keep PHP and MySQL updated
- Make regular backups of your database
- Only give admin access to trusted people
- Use HTTPS (SSL certificate) for your website

---

## Support and Help

### Getting Help:
- Check this README file first
- Review the user guides in the system
- Contact your system administrator
- Report bugs or issues on GitHub

### For Technical Support:
- Visit: https://github.com/MarjovicDEV/LMS-
- Create an issue describing your problem
- Include details about what is not working

---

## License

This project is free to use under the MIT License. See the [LICENSE](LICENSE) file for complete details.

You can use, modify, and distribute this system for free. No payment is required.

---

## Credits

**Developed by:** MarjovicDEV, AslainieLM, and DaintyLamberto

**Built with:** CodeIgniter 4 Framework

**Date:** 2026-03-06

---

## Version History

- **Version 1.0** - Initial release with core features
- Regular updates with new features and bug fixes
- Check the commit history for detailed changes

---

## Future Updates

Planned features for future versions:
- Online quiz and exam system
- Video conferencing integration
- Discussion forums
- Mobile application
- Advanced reporting and analytics
- Email integration
- Attendance tracking
- Parent portal

---

## Thank You

Thank you for choosing MGOD-LMS for your educational institution. We hope this system helps make teaching and learning easier and more organized.

If you find this system helpful, please star the repository on GitHub!

---

**Last Updated:** 2026-04-03
