<?php
require "config.php";

class users extends database
{
    private $table = "users";
    public $name;
    public $email;
    public $pass;

    //function signup
    public function signup()
    {
        if (!empty($this->name) && !empty($this->email) && !empty($this->pass)) {

            $sql = $this->conn->prepare("SELECT id FROM $this->table WHERE email = :email AND client_id = :client_id");
            $sql->execute([':email' => $this->email, ':client_id' => CLIENT_ID]);
            if ($sql->rowCount() == 0) {
                $insert = $this->conn->prepare("INSERT INTO $this->table(username,email,pass,client_id) VALUES(:name,:email,:pass,:client_id)");
                $insert->execute([':name' => $this->name, ':email' => $this->email, ':pass' => $this->pass, ':client_id' => CLIENT_ID]);
?>
                <script>
                    alert("Signup Successfully");
                    document.location = "signup.php";
                </script>
            <?php
            } else {
            ?>
                <script>
                    alert("Duplicate Data");
                    document.location = "signup.php";
                </script>
            <?php
            }
        } else {
            ?>
            <script>
                alert("Fill All The Require Fields");
                document.location = "signup.php";
            </script>
            <?php
        }
    }

    //function login
    public function login()
    {
        if (!empty($this->email) && !empty($this->pass)) {

            $sql = $this->conn->prepare("SELECT id,username,email,pass,userlevel,active FROM $this->table WHERE email = :email AND client_id = :client_id");
            $sql->execute([':email' => $this->email, ':client_id' => CLIENT_ID]);
            if ($sql->rowCount() === 1) {
                $val = $sql->fetch(PDO::FETCH_ASSOC);

                //match password
                if ($this->pass == $val['pass']) {
                    if (isset($val['active']) && $val['active'] == 0) {
                        ?>
                        <script>
                            alert("Your account is deactivated. Please contact support.");
                            document.location = "login.php";
                        </script>
                        <?php
                        exit();
                    }
                    $newtime = date("Y-m-d H:i:s");

                    //update last login time
                    $update = $this->conn->prepare("UPDATE $this->table SET lastlogin = :lastlogin WHERE email = :email AND client_id = :client_id");
                    $update->execute([':lastlogin' => $newtime, ':email' => $this->email, ':client_id' => CLIENT_ID]);

                    session_start();

                    //insert value into session
                    $_SESSION['user_id']  = $val['id'];
                    $_SESSION['username'] = $val['username'];
                    $_SESSION['email'] = $val['email'];
                    $_SESSION['userlevel'] = $val['userlevel'];
            ?>
                    <script>
                        alert("Login Successfully");
                        document.location = "index.php";
                    </script>
                <?php
                } else {
                ?>
                    <script>
                        alert("Wrogn Password");
                        document.location = "login.php";
                    </script>
                <?php
                }
            } else {
                ?>
                <script>
                    alert("Email Not Exist");
                    document.location = "login.php";
                </script>
            <?php
            }
        } else {
            ?>
            <script>
                alert("Fill All The Require Fields");
                document.location = "login.php";
            </script>
<?php
        }
    }
}
?>