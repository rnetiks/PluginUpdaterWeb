<?php
session_start();
mysqli_report(MYSQLI_REPORT_STRICT);
if (User::IsLoggedIn()) {
    header("Location: /");
    exit;
}
$invalid = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $invalid = true;
    if (isset($_POST['Username']) && strlen($_POST['Username']) >= 3
        && isset($_POST['Password']) && strlen($_POST['Password']) >= 4) {

        $username = filter_input(INPUT_POST, 'Username', FILTER_SANITIZE_SPECIAL_CHARS);
        $sql = new mysqli("localhost", "root", getenv('DB_PASSWORD'), 'pud');

        if($sql->connect_error){
            http_response_code(404);
            die("Connection Problem");
        }

        $stmt = $sql->prepare("SELECT u.Username, u.Password, u.Rank FROM users u WHERE Username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0){
            $row = $result->fetch_assoc();
            $hash = $row['Password'];
            $rank = $row['Rank'];
            if(password_verify($_POST['Password'], $hash)){
                $_SESSION['loggedIn'] = 1;
                if($rank >= User::RANK_ADMIN){
                    $_SESSION['Admin'] = 1;
                }
                $_SESSION['Rank'] = $rank;
                session_regenerate_id(true);
                header("Location: /");
                $invalid = false;
                exit;
            }
        }
        $stmt->close();
        $sql->close();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<!--<div class="header">Login</div>-->
<div class="flex center mt25 width100 loginpanel center">
    <form action="/login" method="post">
        <div class="top"></div>
        <!--suppress HtmlFormInputWithoutLabel -->
        <input type="text" name="Username" id="" placeholder="Username">
        <!--suppress HtmlFormInputWithoutLabel -->
        <input type="password" name="Password" id="" placeholder="Password">
        <?php
            if($invalid){
                echo "<div style='color: red'>Invalid Username or Password</div>";
            }
        ?>
        <div class="flex">
            <label for="remember_me">Remember Me</label><input type="checkbox" name="" id="remember_me">
            <button type="submit">Login</button>
        </div>
    </form>
</div>
</body>
</html>