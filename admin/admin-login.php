<?php

session_start();
session_regenerate_id(true);
if(isset($_SESSION) && !empty($_SESSION['email'])){ header('location:../index.php');}
elseif (isset($_SESSION) && !empty($_SESSION['email']) && $_SESSION['userType']== "admin") {header('location:admin-index.php');}

$email;$pass;$errors;$arrLength=0;$name;
require '../includes/connection.php';

if (isset($_POST['btn-login'])){
    $errors=array();

    if (empty($_POST['email'])){
        $errors[]="Email is required";
        /*regex for email address is taken from https://www.w3schools.com/tags/att_input_pattern.asp*/
    }elseif (!(preg_match('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,3}$/',$_POST['email']))){
        /* end of reference */
        $errors[]="Invalid email address: Valid email address is required";
    }else{ $email=checkInput($_POST['email']); }

    if (empty($_POST['pass'])){
        $errors[]="Password is required";
    }elseif (!(preg_match('/.{9,}/',$_POST['pass']))){
        $errors[]="Invalid password: Nine or more characters are required";
    }else{ $pass=checkInput($_POST['pass']); }

    $arrLength=count($errors);

    if ($arrLength==0){
        $checkStatement;
        try {
            $connect = new PDO("mysql:host=$servername;dbname=".$dbName, $username, $password);
            $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            try{
                $checkStatement= $connect->prepare("SELECT * FROM users WHERE email=:email");
                $checkStatement->execute(array(
                    "email"=>$email
                ));

                $result = $checkStatement->fetch(PDO::FETCH_ASSOC);
                $name=$result['name'];
                if(password_verify($pass, $result['pass'])) {

                    /* Reference: https://www.youtube.com/watch?v=KnX0p2Ey3Ek */
                    session_set_cookie_params(time()+700,'/',$servername,false,true);
                    /* End Reference */

                    session_start();
                    $_SESSION['valid'] = true;
                    $_SESSION['timeout'] = time();
                    $_SESSION['URS_AGNT']=md5($_SERVER['HTTP_USER_AGENT']);
                    $_SESSION['email'] = $email;
                    $_SESSION['name']=$name;
                    $_SESSION['userID'] = $result['id'];
                    $_SESSION['userType']= $result['type'];

                    header('location:admin-index.php');
                }else{
                    $errors[]="Email address or password is incorrect";
                    $arrLength=count($errors);
                }
            }
            catch (Exception $ex){
                die("Error in execution of query:" .$ex);
            }
        }
        catch(PDOException $e)
        {
            echo "Connection failed: " . $e->getMessage()."<br>";
        }
    }

}


/* Reference code W3school. Available: https://www.w3schools.com/php/php_form_validation.asp */
function checkInput($val) {
    $val = trim($val);
    $val = stripslashes($val);
    $val = htmlspecialchars($val);
    return $val;
}
/* End Reference */
?><!DOCTYPE html>
<html lang="en">
<?php require 'includes/header.php'; ?>
<div class="container registration-main">
    <!-- Reference : Advance web development assignment 1 -->
    <section class="donate-food-form-sec">
        <h1>Login </h1>
        <?php if ($arrLength>0) { ?>
            <section class="error-sec">
                <h3>There was an error with your submission: </h3>
                <ul>
                    <?php for ($i=0;$i<$arrLength;$i++){ ?>
                        <li><?php echo $errors[$i]; ?></li>
                    <?php } ?>
                </ul>
            </section>
        <?php }?>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" id="form-donate" name="form-donate">
            <div class="form-group">
                <span>Email</span><input type="email" value="" name="email" placeholder="Email" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,3}$" title="Valid email address is required">
            </div>
            <div class="form-group">
                <span>Password</span><input type="password" name="pass" required pattern=".{9,}" title="Nine or more characters are required">
            </div>
            <div class="form-group">
                <input type="submit" id="btn-donate" value="Login" name="btn-login" class="btn btn-primary navbar-btn">
            </div>
        </form>
    </section>
    <!-- End of Reference: Assignment 1 -->
</div>
<?php include 'includes/footer.php';?>

</body>
</html>
