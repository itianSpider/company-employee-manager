<!DOCTYPE html>
<html>
<head>
    <title>New Employee Added</title>
</head>
<body>
    <h1>A new employee has been added to your company</h1>
    <p><strong>First Name:</strong> {{ $employee->first_name }}</p>
    <p><strong>Last Name:</strong> {{ $employee->last_name }}</p>
    <p><strong>Email:</strong> {{ $employee->email ?? 'N/A' }}</p>
    <p><strong>Phone:</strong> {{ $employee->phone ?? 'N/A' }}</p>
</body>
</html>
