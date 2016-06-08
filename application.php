<form name="app" method="post" action="addapplication.php">
    Application name  : <input type="text" name="app_name" id="app_name"/><br/>
    Git Repo Clone URL : <input type="text" name="git_repo" id="git_repo"/><br/>
    Git Uname : <input type="text" name="git_uname" id="git_uname"/><br/>
    Git Pswd : <input type="password" name="git_pswd" id="git_pswd"/><br/>
    <input type="submit" value="Add"/>
</form>
<?php
echo date("Y-m-d h:i:s");
?>