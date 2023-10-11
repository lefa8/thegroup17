<?php
// Initialize the session
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include config file
require_once "config.php";

// Define variables and initialize with empty values
$username = $email = "";
$surname = $student_no = $contact = $module_code = "";
$old_password = $new_password = $confirm_password = "";
$old_password_err = $new_password_err = $confirm_password_err = "";
$surname = $student_no = $contact = $module_code = "";
$username_err = $email_err = $surname_err = $student_no_err = $contact_err = $module_code_err =  "";

// Retrieve user's current information from the database
$id = $_SESSION["id"];
$sql = "SELECT username, email, surname, student_no, contact, module_code FROM users WHERE id = ?";
if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 1) {
            mysqli_stmt_bind_result($stmt, $db_username, $db_email, $db_surname, $db_student_no, $db_contact, $db_module_code);
            mysqli_stmt_fetch($stmt);
            $username = $db_username;
            $email = $db_email;
            $surname = $db_surname;
            $student_no = $db_student_no;
            $contact = $db_contact;
            $module_code = $db_module_code;
        }
    }
    mysqli_stmt_close($stmt);
}

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Validate surname
    if (empty(trim($_POST["surname"]))) {
        $surname_err = "Please enter a surname.";
    } else {
        $surname = trim($_POST["surname"]);
    }

    // Validate student number (you can add more validation if needed)
    if (empty(trim($_POST["student_no"]))) {
        $student_no_err = "Please enter a student number.";
    } else {
        $student_no = trim($_POST["student_no"]);
    }

    // Validate contact (you can add more validation if needed)
    if (empty(trim($_POST["contact"]))) {
        $contact_err = "Please enter a contact number.";
    } else {
        $contact = trim($_POST["contact"]);
    }

    // Validate module code (you can add more validation if needed)
    if (empty(trim($_POST["module_code"]))) {
        $module_code_err = "Please enter a module code.";
    } else {
        $module_code = trim($_POST["module_code"]);
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email address.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate old password
    if (empty(trim($_POST["old_password"]))) {
        $old_password_err = "Please enter the old password.";
    } else {
        $old_password = trim($_POST["old_password"]);
    }

    // Validate new password
    if (empty(trim($_POST["new_password"]))) {
        $new_password_err = "Please enter the new password.";
    } elseif (strlen(trim($_POST["new_password"])) < 6) {
        $new_password_err = "Password must have at least 6 characters.";
    } else {
        $new_password = trim($_POST["new_password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm the password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($new_password_err) && ($new_password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    // Verify the old password
    $sql = "SELECT password FROM users WHERE id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $hashed_password);
                if (mysqli_stmt_fetch($stmt)) {
                    if (password_verify($old_password, $hashed_password)) {
                        // Old password is correct, proceed to update the password
                        if (empty($new_password_err) && empty($confirm_password_err)) {
                            // Prepare an update statement
                            $sql = "UPDATE users SET username = ?, surname = ?, student_no = ?, contact = ?, module_code = ?, email = ?, password = ? WHERE id = ?";
                            if ($stmt = mysqli_prepare($link, $sql)) {
                                // Bind variables to the prepared statement as parameters
                                mysqli_stmt_bind_param($stmt, "sssssssi", $param_username, $param_surname, $param_student_no, $param_contact, $param_module_code, $param_email, $param_password, $param_id);
                                // Set parameters
                                $param_username = $username;
                                $param_surname = $surname;
                                $param_student_no = $student_no;
                                $param_contact = $contact;
                                $param_module_code = $module_code;
                                $param_email = $email;
                                $param_password = password_hash($new_password, PASSWORD_DEFAULT);
                                $param_id = $_SESSION["id"];

                                // Attempt to execute the prepared statement
                                if (mysqli_stmt_execute($stmt)) {
                                    // Update successful, redirect to the profile page
                                    header("location: newpassword.php");
                                    exit();
                                } else {
                                    echo "Oops! Something went wrong. Please try again later.";
                                }
                                // Close statement
                                mysqli_stmt_close($stmt);
                            }
                        }
                    } else {
                        $old_password_err = "The old password is incorrect.";
                    }
                }
            }
        }
        mysqli_stmt_close($stmt);
    }
}

// Close connection
mysqli_close($link);


        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font: 14px sans-serif;
            height: 100%;
            margin: 0;
            padding: 0;
            background-image: url('bg.jpg');
            background-repeat: no-repeat;
            background-position: bottom; /* Move the background down */
            background-size: cover;
        }
        .wrapper {
            width: 500px;
            padding: 60px;
            margin: 0 auto;

            border: 4px solid white;
    border-radius: 20px;
    box-shadow: 0px 50px 20px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        /* Add CSS to make the profile picture round */
        #output-container {
            width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    position: relative;
    overflow: hidden;
    margin: 20px auto;
    margin-left: 100px; /* Adjust margin-left to move the picture to the right */
}
        
        #output {
            width: 100%;
            height: 100%;
        }
         /* Buttons similar to Bootstrap */
  .btn {
   

   margin-top: 25px;
 
   width: 100%;
 
   cursor: pointer;
 
   color: white;
 
   border: none;
 
   font-size: 16px;
 
   border-radius: 40px;
 
   transition: 0.4s;
 
   padding: 7px;
 
   outline: none;
 
   background: #d007d7;
 
 }
 
 
 .btn:hover {
   background-color: #3019c1;
 }
        /* Style for the camera icon */
        #cameraIcon {
            position: absolute;
            top: 0;
            left: 0px;
            width: 100%;
            height: 100%;
            background: url('camera_icon.png') center center no-repeat;
            background-size: 30px;
            opacity: 0.7;
            cursor: pointer;
        }
        /* Hide the file input */
        #profilePicture {
            display: none;
        }
        /* Center the title */
        h1 {
            text-align: center;
            color: white;
        }
        h2{
            margin-top: 30px; /* Move the text downward by increasing the top margin */
            margin-bottom: 15px; /* Move the text upward by increasing the bottom margin */
            color: purple;
            text-align: center;
        }
        h3 {
            margin-top: 25px; /* Move the text downward by increasing the top margin */
            color: purple;
        }

  .form-group label {
    
    color: white;
   
}
        .form-control {
            width: 100%;
            display: block;
    padding: 10px; /* Decreased padding */
    font-size: 16px; /* Decreased font-size */
    border: 1px solid #d60808;
    border-radius: 30px;
        }
        .links {
            text-align: center;
            color: white;
        }
    </style>
</head>
<body>
    <div class="wrapper">
    <h1>Edit Profile</h1>
    <form action="update_profile.php" method="POST" enctype="multipart/form-data">
            <label for="profilePicture" id="output-container">
                <input type="file" accept="image/*" onchange="loadFile(event)" name="profile_picture" id="profilePicture">
                <img id="output" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSoiNSuN7akpj7D0Z3M8_-_cehNrUgrTU_sjvG5cGU4-A&s" alt="Profile Picture"/>
                <!-- Replace 'camera_icon.png' with your camera icon image path -->
                <label id="cameraIcon" for="profilePicture"></label>
            </label>
            <script>
                var loadFile = function(event) {
                    var image = document.getElementById('output');
                    image.src = URL.createObjectURL(event.target.files[0]);
                };
            </script>
        
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            modify this code so that when i click save changes it only saves the details above password
            <h3>BASIC USER INFORMATION</h3>
<form action="update_profile.php" method="POST">
    <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>">
    </div>
    <div class="form-group">
        <label for="surname">Surname:</label>
        <input type="text" id="surname" name="surname" class="form-control" value="<?php echo htmlspecialchars($surname); ?>">
        <span class="invalid-feedback"><?php echo $surname_err; ?></span>
    </div>

    <div class="form-group">
            <label for="student_no">Student Number:</label>
            <input type="text" id="student_no" name="student_no" class="form-control" value="<?php echo htmlspecialchars($student_no); ?>">
            <span class="invalid-feedback"><?php echo $student_no_err; ?></span>
        </div>
        
        <div class="form-group">
            <label for="contact">Contact:</label>
            <input type="text" id="contact" name="contact" class="form-control" value="<?php echo htmlspecialchars($contact); ?>">
            <span class="invalid-feedback"><?php echo $contact_err; ?></span>
        </div>
        
        <div class="form-group">
            <label for="module_code">Module Code:</label>
            <input type="text" id="module_code" name="module_code" class="form-control" value="<?php echo htmlspecialchars($module_code); ?>">
            <span class="invalid-feedback"><?php echo $module_code_err; ?></span>
        </div>

    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>">
    </div>
    
    <div class="form-group">

  
    <input type="submit" class="btn btn-primary" name="save_basic_info" value="Save First 6 Details">
            </div>
 
</form>




<h2>CHANGE PASSWORD</h2>
<form action="newpassword.php" method="POST">
    <div class="form-group">
        <label for="old_password">Old Password:</label>
        <input type="password" name="old_password" id="old_password" class="form-control <?php echo (!empty($old_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $old_password; ?>">
        <span class="invalid-feedback"><?php echo $old_password_err; ?></span>
    </div>
    <div class="form-group">
        <label for="new_password">New Password:</label>
        <input type="password" name="new_password" id="new_password" class="form-control <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $new_password; ?>">
        <span class="invalid-feedback"><?php echo $new_password_err; ?></span>
    </div>

    <div class="form-group">
        <label for="confirm_password">Confirm Password:</label>
        <input type="password" name="confirm_password" id="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
        <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
    </div>
    <form action="update_profile.php" method="POST">
 

   
    <div class="form-group">

  
    <input type="submit" class="btn btn-primary" name="save_basic_info" value="Save First 6 Details">
            </div>
        
        <input type="submit" class="btn btn-primary" value="Change profile">
        <input type="reset" class="btn btn-primary" value="Reset" onclick="window.location.href='profile.php';">
    </div>
</form>