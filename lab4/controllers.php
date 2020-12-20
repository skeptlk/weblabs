<?php

function controller($name, $mysqli) {
    $name($mysqli);
}

function home ($mysqli) {
    if ($id = auth($mysqli)) {
        $query = "SELECT * FROM users WHERE id='$id'";
        $result = $mysqli->query($query);
        $user = $result->fetch_assoc();
        $photo = "data:".$user["photo_ext"].";charset=utf8;base64," . base64_encode($user["photo"]);

        return view ("home_logged", [
            "first_name" => $user["first_name"],
            "last_name" => $user["last_name"],
            "email" => $user["email"],
            "photo" => $photo
        ]);
    } else {
        return view("home");
    }
}

function myaccount ($mysqli) {
    $id = auth($mysqli);
    if ($id > 0) {
        $query = "SELECT * FROM users WHERE id='$id'";
        $result = $mysqli->query($query);
        $user = $result->fetch_assoc();

        return view("myaccount", [
            "user_id" => $user["id"],
            "first_name" => $user["first_name"],
            "last_name" => $user["last_name"],
            "email" => $user["email"]
        ]);

    } else if ($id == -1) {
        return view("error", ["error" => "Account is not activated!"]);
    } else {
        return view("error", ["error" => "You are not authourized!"]);
    }
}

function login ($mysqli) {
    return view("login");
}

function signup ($mysqli) {
    return view("signup");
}

function restore ($mysqli) {
    return view("restore");
}

function restore_pass ($mysqli) {
    return view("restore_pass");
}

