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
                $hashedPass = password_hash($this->pass, PASSWORD_DEFAULT);
                $insert->execute([':name' => $this->name, ':email' => $this->email, ':pass' => $hashedPass, ':client_id' => CLIENT_ID]);

                // Create admin notification
                $notif = $this->conn->prepare("INSERT INTO notifications (userid, title, message, type, client_id) VALUES (NULL, :title, :message, 'user_created', :client_id)");
                $notif->execute([
                    ':title' => "New User Registered",
                    ':message' => "A new user account was registered: " . htmlspecialchars($this->name) . " (" . htmlspecialchars($this->email) . ").",
                    ':client_id' => CLIENT_ID
                ]);
?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Signed Up Successfully!',
                            confirmButtonColor: '#16a34a'
                        }).then(() => {
                            document.location = "signup.php";
                        });
                    });
                </script>
            <?php
            } else {
            ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Registration Failed',
                            text: 'Email already exists. Use another email.',
                            confirmButtonColor: '#ea580c'
                        }).then(() => {
                            document.location = "signup.php";
                        });
                    });
                </script>
            <?php
            }
        } else {
            ?>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                document.addEventListener("DOMContentLoaded", () => {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Fields Required',
                        text: 'Please fill all the required fields.',
                        confirmButtonColor: '#ea580c'
                    }).then(() => {
                        document.location = "signup.php";
                    });
                });
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

                //match password using password_verify or fallback to plain text comparison
                $passwordMatch = false;
                if (password_verify($this->pass, $val['pass'])) {
                    $passwordMatch = true;
                } else if ($this->pass === $val['pass']) {
                    // Password was stored in plain text, let's match it and upgrade it to a secure hash!
                    $passwordMatch = true;
                    $newHashedPass = password_hash($this->pass, PASSWORD_DEFAULT);
                    $upgrade = $this->conn->prepare("UPDATE $this->table SET pass = :pass WHERE id = :id");
                    $upgrade->execute([':pass' => $newHashedPass, ':id' => $val['id']]);
                }

                if ($passwordMatch) {
                    if (isset($val['active']) && $val['active'] == 0) {
                        ?>
                        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                        <script>
                            document.addEventListener("DOMContentLoaded", () => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Account Deactivated',
                                    text: 'Your account is deactivated. Please contact support.',
                                    confirmButtonColor: '#ea580c'
                                }).then(() => {
                                    document.location = "login.php";
                                });
                            });
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
                    if ((int)$val['userlevel'] === -1) {
                        header("Location: admin.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit();
                } else {
                ?>
                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Login Failed',
                                text: 'Wrong password combination. Please try again.',
                                confirmButtonColor: '#ea580c'
                            }).then(() => {
                                document.location = "login.php";
                            });
                        });
                    </script>
                <?php
                }
            } else {
                ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Login Failed',
                            text: 'Email does not exist. Register for a new account.',
                            confirmButtonColor: '#ea580c'
                        }).then(() => {
                            document.location = "login.php";
                        });
                    });
                </script>
            <?php
            }
        } else {
            ?>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                document.addEventListener("DOMContentLoaded", () => {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Fields Required',
                        text: 'Please fill all the required fields.',
                        confirmButtonColor: '#ea580c'
                    }).then(() => {
                        document.location = "login.php";
                    });
                });
            </script>
<?php
        }
    }
}
?>