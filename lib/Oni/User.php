<?php

namespace Oni;

/**
 * Description of User
 *
 * @author guillaume
 */
class User {

  public $userId;
  public $username;
  private $email;
  private $password;

  public function __construct($username, $password = "changeme", $email_address = "changeme@changme.com") {

    $this->userId = $user_id;
    $this->username = $username;
    $this->password = $password;
    $this->email = $email_address;
    
    $this->checkOrInsert();
  }

  
  private function checkOrInsert()
  {
    //echo $this->username . " ";
    $ids = array();
    $usernames = array($this->username);
    
    $error = user_get_id_name($ids, $usernames);
    
    if($error == FALSE)
    { 
      $this->userId = $ids[0];
    }
    else
    { 
      $this->insert();
    } 
    
  }
  
  private function insert() {

    $group_id = 2;
    $language = "fr";
    $user_type = "user";


    $user_row = array(
      'username' => $this->username,
      'user_password' => phpbb_hash($this->password),
      'user_email' => $this->email,
      'group_id' => (int) $group_id,
      //  'user_timezone'         => (float) $timezone,
      //  'user_dst'              => $is_dst,
      'user_lang' => $language,
      'user_type' => USER_NORMAL,
//    'user_actkey'           => $user_actkey,
        //   'user_ip'               => $user_ip,
        //   'user_regdate'          => $registration_time,
        //   'user_inactive_reason'  => $user_inactive_reason,
        //  'user_inactive_time'    => $user_inactive_time,
    );
     
// all the information has been compiled, add the user
// tables affected: users table, profile_fields_data table, groups table, and config table.
    $user_id = user_add($user_row);

    $this->userId = $user_id;
  }

}

?>
