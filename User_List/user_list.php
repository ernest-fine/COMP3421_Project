<?php

// Start the session
session_start();

// Config Database
require_once "../config.php";

// Check if user is off-online, if yes, redirect to login page
if(!isset($_SESSION["online"]) || $_SESSION["online"] === false){
    header("location: /");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Store user input to search variable
    if(empty(trim($_POST["search"]))){
        $search = "";
    } else{
        $search = trim($_POST["search"]);
    }
}

// Query database for user icon
$result = mysqli_query($link, "SELECT icon, caption FROM users WHERE id = '".$_SESSION['id']."'");
$currentuser = mysqli_fetch_array($result, MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Together</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="./user_list.css">
</head>
<body>
    <div class="wrapper">
        <section class="user-page">

            <!-- User Account Information -->
            <div class="account">
                <div class="my-account-info">
                    <?php echo '<img src="'.$currentuser["icon"].'" alt="'.$_SESSION['username'].'" class="image-icon">'; ?>
					<div class="content">
                        <div><?php echo $_SESSION['username']?></div>
                        <div id="caption"><?php echo empty($currentuser["caption"])?"Welcome to use Chat Together!":$currentuser["caption"];?><span><button id="pen-button" onclick="clicked_pen()"><i class="fa fa-pencil"></i></button></span></div>
                        <p><i class="fa fa-circle" id="my-status-indicator"></i> &nbsp Active</p>
                    </div>
                </div>
                <div class="button">
                    <a id="logout-button" href="/logout.php"><i class="fa fa-sign-out" aria-hidden="true"></i></a> 
                </div>
            </div>

            <!-- Search -->
            <form class="search" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="text" placeholder="Enter name to search...." name="search" value="<?php echo isset($search)?$search:""; ?>">
                <button type="submit"><i class="fa fa-search"></i></button>
            </form>

            <!-- Friend Account Information -->
            <div class="user-list">
                <?php
                    // Query all users in the database
                    $sql = "SELECT id, username, icon, email, caption FROM users WHERE id != ?";

                    // Execute SQL
                    if($stmt = mysqli_prepare($link, $sql)){
                        mysqli_stmt_bind_param($stmt, "i", $param_id);
                        $param_id = $_SESSION['id'];
                        if(mysqli_stmt_execute($stmt)){
                            mysqli_stmt_store_result($stmt);
                            mysqli_stmt_bind_result($stmt, $id, $username, $icon, $email, $caption);
                            $number_of_list = mysqli_stmt_num_rows($stmt);

                            $counter = 0;
                            // list all the query result
                            for($x = 0; $x < $number_of_list; $x++){
                                if(mysqli_stmt_fetch($stmt)){
                                    if(empty($search) || str_contains($username, $search)){
                                        $counter += 1;
                                        $input = empty($caption)?"Welcome to use Chat Together!":$caption;
                                        echo 
										'<a href="./redirect.php?id='.$id.'" class="accountbutton">
											<div class="account">
												<div class="account-info">
													<img src="'.$icon.'" alt="'.$username.'" class="image-icon">
													<div class="content">
														<span>'.$username.'</span>
														<p>'.$input.'</p>
													</div>
												</div>
											</div>
                                        </a>';
                                    }
                                }
                            }

                            // Provide negative feedback when no user is found from the query
                            if ($counter == 0){
                                echo "<h4>No user is found!</h4>";
                            }
                        }
                    }

                    mysqli_stmt_close($stmt);

                ?>
            </div>

            <!-- The Modal -->
            <div id="myModal">

                <!-- Modal content -->
                <div class="modal-content">
                    <form action="./change_caption.php" method="POST">
                        <div class="modal-header">
                            <h5>Edit Caption</h5>
                            <button type="button" class="close" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="input-group mb-3 ">
                            <input type="text" class="form-control" placeholder="New caption" name="new-caption">
                            <input class="btn btn-outline-secondary" type="submit" value="Submit">
                        </div>
                    </form>
                </div>

            </div>
        </section>
    </div>
</body>

<script>
        // Get the modal
        var modal = document.getElementById("myModal");

        // Get the button that opens the modal
        var button = document.getElementsByClassName("close")[0];

        // When the user clicks the button, open the modal 
        function clicked_pen() {
            modal.style.display = "block";
        }

        button.onclick = function(){
            modal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>

</html>
