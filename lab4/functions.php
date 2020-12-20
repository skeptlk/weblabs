<?php 

function view ($name, $params = []) 
{
    $content = file_get_contents("templates/$name.html");
    foreach ($params as $param => $value) {
        $content = str_replace("{{$param}}", $value, $content);
    }
    echo $content;
}

function api_signup($mysqli)
{
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $first_name = $_POST["first_name"];
        $last_name =  $_POST["last_name"];
        $email = $_POST["email"];
        $password = $_POST["password"];

        $email_exits_check = "SELECT * FROM `users` WHERE email='$email'";
        $result = $mysqli->query($email_exits_check);

        if (!empty($first_name) && !empty($last_name) &&
            !empty($password)   && !empty($email) && $result) 
        {
            if ($result->num_rows > 0)
                return view("error", ["error" => "This email already registered!"]);

            $first_name = $mysqli->real_escape_string(substr($first_name, 0, 255));
            $last_name  = $mysqli->real_escape_string(substr($last_name, 0, 255));
            $email      = $mysqli->real_escape_string(substr($email, 0, 255));
            $password   = $mysqli->real_escape_string(substr($password, 0, 255));

            // Validation 
            if ($first_name === "" || $last_name === "")
                return view("error", ["error" => "Please, provide valid name!"]);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
                return view("error", ["error" => "Please, provide valid email!"]);
            if (strlen($password) < 4)
                return view("error", ["error" => "Password is not strong enough!"]);


            // put the new user in database
            $token = md5(rand().time()); // random activation token
            $passhash = password_hash($password, PASSWORD_BCRYPT);
            
            $query = "INSERT INTO users (first_name, last_name, email, password, token)
                VALUES ('$first_name', '$last_name', '$email', '$passhash', '$token')";
            $result = $mysqli->query($query);

            if (!$result) {
                echo("Error: " . $mysqli->error);
                return; 
            }
            else {
                return view("verification_request", [
                    "activation_link" => "http://localhost/api_activate/$token"
                ]);
            }
        }

    } 
    else return view("404");
}

function api_edit_profile($mysqli)
{
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id         = $mysqli->real_escape_string($_POST["id"]);
        $email      = $mysqli->real_escape_string($_POST["email"]);
        $first_name = $mysqli->real_escape_string($_POST["first_name"]);
        $last_name  = $mysqli->real_escape_string($_POST["last_name"]);
        $photoContent = "";

        // var_dump($_FILES);
        // die;

        if(!empty($_FILES["photo"]["name"])) { 
            // Get file info 
            $photo = $_FILES['photo']['tmp_name']; 
            $photoContent = addslashes(file_get_contents($photo)); 
            $photoExt = $mysqli->real_escape_string($_FILES["photo"]["type"]);
        }

        if ($photoContent !== "") {
            $q_update = "UPDATE users SET email='$email', first_name='$first_name', last_name='$last_name', photo='$photoContent', photo_ext='$photoExt' WHERE id='$id'";
        } else {
            $q_update = "UPDATE users SET email='$email', first_name='$first_name', last_name='$last_name' WHERE id='$id'";
        }
        $result = $mysqli->query($q_update);

        if ($result)
            header("Location: /myaccount");
        else view("error", ["error" => $mysqli->error]);
    } 
    else view("404");
}

function api_login($mysqli)
{
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];
        $passw = $_POST['password'];

        $query = "SELECT * FROM users WHERE email='$email'";
        $result = $mysqli->query($query);
        
        if (!$result || $result->num_rows == 0) 
            return view("404");

        $user = $result->fetch_assoc();
        $newtoken = md5(rand().time()); 

        setcookie('id', $user['id']);
        setcookie('first_name', $user['first_name']);
        setcookie('last_name', $user['last_name']);
        setcookie('email', $user['email']);
        setcookie('token', $newtoken);

        $q_update_token = "UPDATE users SET token='$newtoken' WHERE email='$email'";
        $result = $mysqli->query($q_update_token);
        if (!$result)
            return view("error", ["error" => $mysqli->error]);

        header("Location: /");

        // return view("home_logged", [
        //     'first_name' => $user['first_name'],
        //     'last_name' => $user['last_name'],
        //     'email' => $user['email']
        // ]);

    } else {
        return view("404");
    }
}

function api_request_pass_restore($mysqli)
{
    $email = $_POST['email'];
    $query = "SELECT * FROM users WHERE email='$email'";
    $result = $mysqli->query($query);
    
    if (!$result || $result->num_rows == 0) 
        return view("error", ["error" => "Email not found!"]);
    
    $user = $result->fetch_assoc();

    if ($user["is_verified"] == 0)
        return view("error", ["error" => "Account is not activated!"]);
    
    $restore_code = rand(100000, 999999);

    $q_save_code = "UPDATE users SET restore_code = $restore_code WHERE email='$email'";
    $mysqli->query($q_save_code);

    echo $restore_code;
}

function api_restore_password($mysqli)
{
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
    
        $email        = trim($mysqli->real_escape_string($_POST['email']));
        $newpass      = trim($mysqli->real_escape_string($_POST['newpass']));
        $repeatpass   = trim($mysqli->real_escape_string($_POST['repeatpass']));
        $restore_code = trim($mysqli->real_escape_string($_POST['code']));

        $query = "SELECT * FROM users WHERE email='$email'";
        $result = $mysqli->query($query);
        
        if (!$result || $result->num_rows == 0) 
            return view("404");

        $user = $result->fetch_assoc();

        if ($user['restore_code'] != $restore_code)
            return view("error", ["error" => "Code was incorrect!"]);
        
        if ($newpass !== $repeatpass)
            return view("error", ["error" => "Passwords do not match!"]);

        $passhash = password_hash($newpass, PASSWORD_BCRYPT);

        $q_update_pass = "UPDATE users SET password='$passhash', restore_code='' WHERE email='$email'";
        $result = $mysqli->query($q_update_pass); // TODO : handle error

        return view("success", ['message' => 'Password updated successfully']);
    
    } else return view("404");
}

function api_logout($mysqli)
{
    setcookie('id', "", time() - 3600);
    setcookie('first_name', "", time() - 3600);
    setcookie('last_name', "", time() - 3600);
    setcookie('email', "", time() - 3600);
    setcookie('token', "", time() - 3600);
    
    header("Location: /");
}

function api_activate($mysqli, $path) 
{
    if (count($path) < 2)
        return view("404");
    $token = $path[1];

    $query = "SELECT * FROM users WHERE token='$token'";
    $result = $mysqli->query($query);
    
    if ($result->num_rows !== 1) {
        return view("404");
    } else {
        $user = $result->fetch_assoc();
        $id = $user['id'];
        $query = "UPDATE users SET is_verified = 1 WHERE id='$id'";
        $result = $mysqli->query($query);

        if ($result) {
            // now user is activated, but not logged in
            $username = $user["first_name"] ." ". $user["last_name"];
            return view('verification_success', ['username' => $username ]);
        }
    }
}

function auth($mysqli) 
{
    if (isset($_COOKIE['token'])) {
        $token = $mysqli->real_escape_string($_COOKIE['token']);
        $email = $_COOKIE['email'];
        $query = "SELECT id, is_verified FROM users WHERE token='$token'";
        $result = $mysqli->query($query);
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($user["is_verified"] == 0)
                return -1;
            else return $user["id"];
        }
        else return false;
    } 
    else return false;
}


function page_header () 
{
    ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Document</title>
            <link rel="stylesheet" href="/public/css/bootstrap.css">
            <script src="/public/js/bootstrap.js"></script>
        </head>
        <body>
    <?php
}

function page_footer()
{
    ?> 
        </body>
        </html>
    <?php
}

