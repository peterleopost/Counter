<?php
session_start();
if(isset($_POST['pass'])){
 if($_POST['pass']=="juna123"){
  $_SESSION['login']=true;
  header("Location: dashboard.php");
 }
}
?>
<form method="post">
<input type="password" name="pass">
<button>Login</button>
</form>
