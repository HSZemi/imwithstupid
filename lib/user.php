<?php 

!defined("PASSSALT") ? define("PASSSALT", "9a158ed0cb479a75d0b0f83bb8508900") : '';

// login: $user as string (username), $pass as string
function user_login($link, $user, $pass){
    $user = mysqli_real_escape_string($link, $user);
    $pass = mysqli_real_escape_string($link, $pass);
    
    $query = "SELECT ID, password FROM iwsUsers WHERE username LIKE '$user'";
    $result = mysqli_query($link, $query) or die("user_login: Anfrage fehlgeschlagen: " . mysqli_error($link));
    if($row = mysqli_fetch_array($result)){
	$passwd_enc = $row['password'];
	$user_id = $row['ID'];
	
	mysqli_free_result($result);
    
	if (CRYPT_MD5 == 1){
		if(crypt($pass,"$1$".PASSSALT) != $passwd_enc){
			return -1;
		} else {
			return $user_id;
		}
	} else {
		echo "MD5 not available.\n<br>";
	}
    } else {
	//nothing found
	mysqli_free_result($result);
	return -1;
    }
}


// take user_id as string or int, return corresponding username as string
function get_user_by_id($link, $user_id){
    $user_id = intval($user_id);
    $query = "SELECT username FROM iwsUsers WHERE ID=$user_id";
    $result = mysqli_query($link, $query) or die("get_user_by_id: Anfrage fehlgeschlagen: " . mysqli_error($link));
    if($row = mysqli_fetch_array($result)){
	$username = $row['username'];
	mysqli_free_result($result);
	
	return $username;
    } else {
	return "";
    }
}

// take username as string, return corresponding user id as int or -1 if none exists
function get_id_of_user($link, $username){
    $username = mysqli_real_escape_string($link, $username);
    $query = "SELECT ID FROM iwsUsers WHERE username LIKE '".$username."'";
    $result = mysqli_query($link, $query) or die("get_id_of_user: Anfrage fehlgeschlagen: " . mysqli_error($link));
    if($row = mysqli_fetch_array($result)){
	$user_id = $row['ID'];
	mysqli_free_result($result);
    
	return intval($user_id);
    } else {
        return -1;
    }
}

// take username as string and password as string and make a user available with those credentials
function create_or_update_user($link, $user, $pass){
    $user = mysqli_real_escape_string($link, $user);
    $pass = mysqli_real_escape_string($link, $pass);
    
    if (CRYPT_MD5 == 1){
        $pass = crypt($pass,"$1$".PASSSALT);
        
        $user_id = get_id_of_user($link, $user);
        
        if($user_id >= 0){
            $query = "UPDATE iwsUsers SET password='".$pass."' WHERE ID=$user_id;";
        } else {
            $query = "INSERT INTO iwsUsers(username, password) VALUES ('$user', '$pass');";
        }
        $result = mysqli_query($link, $query);
        if(!$result){
            echo "create_or_update_user: Anfrage fehlgeschlagen: " . mysqli_error($link) . "<br/>";
            return false;
        }
        return true;
    } else {
        echo "MD5 not available.\n<br>";
        return false;
    }
}

// take username as string and password as string and delete corresponding user if the
// credentials are valid
function delete_user($link, $user, $pass){
    if(user_login($link, $user, $pass) > -1){
        $user = mysqli_real_escape_string($link, $user);
        $query = "DELETE FROM iwsUsers WHERE username LIKE '".$user."'";
        $result = mysqli_query($link, $query);
        if(!$result){
            echo "delete_user: Anfrage fehlgeschlagen: " . mysqli_error($link) . "<br/>";
            return false;
        }
        return true;
    } else {
        return false;
    }
}

?>