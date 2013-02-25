<?php 


  // Keep in mind when working with sessions that it is generally
  // inadvisable to store DB-related objects in sessions
  class Session {

    private $logged_in = false;
    public $user_id;

    public function __construct() {
      session_start();
      $this->check_login();
      if ($this->logged_in) {
          
      } else {

      }
    } 

    public function is_logged_in() {
      return $this->logged_in;
    }

    public function login($user) {
      // database should find user based on username/password
      if ($user) {
        $this->user_id = $_SESSION['user_id'] = $user->id;
        $this->logged_in = true; 
      }
    }

    public function logout() {
      unset($_SESSION['user_id']);
      unset($this->user_id);
      $this->logged_in = false;

      $_SESSION = array(); //destroy all of the session variables
      if (ini_get("session.use_cookies")) {
          $params = session_get_cookie_params();
          setcookie(session_name(), '', time() - 42000,
              $params["path"], $params["domain"],
              $params["secure"], $params["httponly"]
          );
      }
      session_destroy();
      session_write_close();
    }

    private function check_login() {
      if (isset($_SESSION['user_id'])) {
        $this->user_id = $_SESSION['user_id'];
        $this->logged_in = true;
      } else {
        unset($this->user_id);
        $this->logged_in = false;
      }
    }

  }

  $session = new Session();

 ?>