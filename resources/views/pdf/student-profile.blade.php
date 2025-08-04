<!DOCTYPE html>
<html>
<head>
    <title>Student Profile</title>
    <style>
        body { color:black; font-family: DejaVu Sans, sans-serif; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        td, th { border: 1px solid #ccc; padding: 10px; text-align: left; }
    </style>
</head>
<body>
    <h2>Student Profile</h2>

    @if($profile->image_path)
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="{{ public_path($profile->image_path) }}" width="150" height="150" style="border-radius: 10px;" alt="Student Image">
        </div>
    @endif

    <table>
        <tr><th>Reg Number</th><td>{{ $profile->student->reg_number }}</td></tr>
        <tr><th>Surname</th><td>{{ $profile->surname }}</td></tr>
        <tr><th>Middle Name</th><td>{{ $profile->middle_name }}</td></tr>
        <tr><th>Last Name</th><td>{{ $profile->last_name }}</td></tr>
        <tr><th>Gender</th><td>{{ $profile->gender }}</td></tr>
        <tr><th>Date of Birth</th><td>{{ $profile->dob }}</td></tr>
        <tr><th>Phone</th><td>{{ $profile->phone }}</td></tr>
        <tr><th>NIN</th><td>{{ $profile->nin }}</td></tr>
        <tr><th>Contact Address</th><td>{{ $profile->contact_address }}</td></tr>
        <tr><th>State</th><td>{{ $profile->state }}</td></tr>
        <tr><th>LGA</th><td>{{ $profile->lga }}</td></tr>
        <tr><th>Home Town</th><td>{{ $profile->home_town }}</td></tr>
        <tr><th>Blood Group</th><td>{{ $profile->blood_group }}</td></tr>
        <tr><th>Genotype</th><td>{{ $profile->genotype }}</td></tr>
        <tr><th>Religion</th><td>{{ $profile->religion }}</td></tr>
        <tr><th>Department</th><td>{{ $profile->department }}</td></tr>
        <tr><th>Year</th><td>{{ $profile->year }}</td></tr>
    </table>
</body>

</html>
