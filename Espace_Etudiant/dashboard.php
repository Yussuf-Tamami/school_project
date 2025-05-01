<?php
// Example PHP code to generate notifications for new grades
$newGradeNotifications = [
    [
        'course' => 'Web Development',
        'message' => 'A new grade has been added. Check your updated results.',
        'time' => '2 hours ago',
        'icon' => 'check-circle',
        'color' => '#3498db'
    ],
    // More notifications...
];

foreach ($newGradeNotifications as $notification) {
    echo '<div class="notification-item">';
    echo '<div class="notification-icon" style="background-color: '.$notification['color'].'">';
    echo '<i class="fas fa-'.$notification['icon'].'"></i>';
    echo '</div>';
    echo '<div class="notification-content">';
    echo '<h4>New Grade Added</h4>';
    echo '<p>'.$notification['message'].' <strong>'.$notification['course'].'</strong>.</p>';
    echo '</div>';
    echo '<div class="notification-time">'.$notification['time'].'</div>';
    echo '</div>';
}


// Example PHP code to generate grades table
$grades = [
    ['subject' => 'Web Development', 'professor' => 'Dr. Smith', 'grade' => 16.5, 'status' => 'Passed'],
    ['subject' => 'Database Systems', 'professor' => 'Prof. Johnson', 'grade' => 15.0, 'status' => 'Passed'],
    // More grades...
];

foreach ($grades as $grade) {
    $gradeClass = ($grade['grade'] >= 10) ? 'grade-high' : 'grade-low';
    echo '<tr>';
    echo '<td>'.$grade['subject'].'</td>';
    echo '<td>'.$grade['professor'].'</td>';
    echo '<td class="'.$gradeClass.'">'.$grade['grade'].'</td>';
    echo '<td>'.$grade['status'].'</td>';
    echo '<td><a href="#">View Details</a></td>';
    echo '</tr>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar/Navbar */
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid #34495e;
        }
        
        .sidebar-header h2 {
            font-size: 1.3rem;
        }
        
        .sidebar-header p {
            font-size: 0.8rem;
            color: #bdc3c7;
        }
        
        .nav-menu {
            margin-top: 20px;
        }
        
        .nav-item {
            padding: 12px 20px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .nav-item:hover {
            background-color: #34495e;
        }
        
        .nav-item.active {
            background-color: #3498db;
        }
        
        .nav-item i {
            margin-right: 10px;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .header h1 {
            color: #2c3e50;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
        }
        
        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .card h3 {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .card p {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2c3e50;
        }
        
        /* Notifications Section */
        .notifications {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .notifications h2 {
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .notification-item {
            padding: 10px 0;
            border-bottom: 1px solid #ecf0f1;
            display: flex;
            align-items: center;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-icon {
            width: 40px;
            height: 40px;
            background-color: #3498db;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
        }
        
        .notification-content h4 {
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .notification-content p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .notification-time {
            color: #bdc3c7;
            font-size: 0.8rem;
            margin-left: auto;
        }
        
        /* Grades Table */
        .grades-table {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .grades-table h2 {
            padding: 20px;
            color: #2c3e50;
            border-bottom: 1px solid #ecf0f1;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        
        th {
            background-color: #f8f9fa;
            color: #7f8c8d;
            font-weight: 600;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .grade-high {
            color: #27ae60;
            font-weight: bold;
        }
        
        .grade-low {
            color: #e74c3c;
            font-weight: bold;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar/Navbar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Student Dashboard</h2>
                <p>Welcome back, [Student Name]</p>
            </div>
            <div class="nav-menu">
                <div class="nav-item">
                    <i class="fas fa-home"></i> Home
                </div>
                <div class="nav-item active">
                    <i class="fas fa-book"></i> Courses
                </div>
                <div class="nav-item">
                    <i class="fas fa-clipboard-list"></i> Assignments
                </div>
                <div class="nav-item">
                    <i class="fas fa-chart-line"></i> Grades
                </div>
                <div class="nav-item">
                    <i class="fas fa-calendar-alt"></i> Schedule
                </div>
                <div class="nav-item">
                    <i class="fas fa-cog"></i> Settings
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Dashboard</h1>
                <div class="user-profile">
                    <img src="https://via.placeholder.com/40" alt="Profile">
                    <span>[Student Name]</span>
                </div>
            </div>
            
            <!-- Dashboard Cards -->
            <div class="dashboard-cards">
                <div class="card">
                    <h3>Current Semester</h3>
                    <p>Semester 5</p>
                </div>
                <div class="card">
                    <h3>Courses Enrolled</h3>
                    <p>8</p>
                </div>
                <div class="card">
                    <h3>Average Grade</h3>
                    <p>15.2</p>
                </div>
            </div>
            
            <!-- Notifications Section -->
            <div class="notifications">
                <h2>Notifications</h2>
                
                <!-- PHP would generate these notifications dynamically -->
                <!-- Example: When a new grade is added to a subject -->
                <div class="notification-item">
                    <div class="notification-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="notification-content">
                        <h4>New Grade Added</h4>
                        <p>A new grade has been added to <strong>Web Development</strong>. Check your updated results.</p>
                    </div>
                    <div class="notification-time">2 hours ago</div>
                </div>
                
                <!-- Another notification example -->
                <div class="notification-item">
                    <div class="notification-icon" style="background-color: #e74c3c;">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="notification-content">
                        <h4>Assignment Deadline</h4>
                        <p>Your assignment for <strong>Database Systems</strong> is due in 3 days.</p>
                    </div>
                    <div class="notification-time">1 day ago</div>
                </div>
                
                <!-- Another grade notification -->
                <div class="notification-item">
                    <div class="notification-icon" style="background-color: #2ecc71;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="notification-content">
                        <h4>New Grade Added</h4>
                        <p>A new grade has been added to <strong>Data Structures</strong>. Check your updated results.</p>
                    </div>
                    <div class="notification-time">3 days ago</div>
                </div>
            </div>
            
            <!-- Grades Table -->
            <div class="grades-table">
                <h2>My Grades</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Professor</th>
                            <th>Grade</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- PHP would generate these rows dynamically -->
                        <tr>
                            <td>Web Development</td>
                            <td>Dr. Smith</td>
                            <td class="grade-high">16.5</td>
                            <td>Passed</td>
                            <td><a href="#">View Details</a></td>
                        </tr>
                        <tr>
                            <td>Database Systems</td>
                            <td>Prof. Johnson</td>
                            <td class="grade-high">15.0</td>
                            <td>Passed</td>
                            <td><a href="#">View Details</a></td>
                        </tr>
                        <tr>
                            <td>Data Structures</td>
                            <td>Dr. Williams</td>
                            <td class="grade-high">14.8</td>
                            <td>Passed</td>
                            <td><a href="#">View Details</a></td>
                        </tr>
                        <tr>
                            <td>Algorithms</td>
                            <td>Prof. Brown</td>
                            <td class="grade-low">9.5</td>
                            <td>Failed</td>
                            <td><a href="#">View Details</a></td>
                        </tr>
                        <tr>
                            <td>Operating Systems</td>
                            <td>Dr. Davis</td>
                            <td class="grade-high">13.2</td>
                            <td>Passed</td>
                            <td><a href="#">View Details</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>