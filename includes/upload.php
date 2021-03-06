<?php 

  require_once('database.php');

  class Upload extends DatabaseObject {

    protected static $table_name = "product_image";
    protected static $db_fields = array('product_id', 'filename', 'type', 'size');

    public $product_id;
    public $filename;
    public $type; 
    public $size;
    
    private $temp_path;
    protected $upload_dir = "img";
    public $errors = array();
    protected $upload_errors = array(
      UPLOAD_ERR_OK         => "No errors.",
      UPLOAD_ERR_INI_SIZE   => "Larger than upload_max_filesize.",
      UPLOAD_ERR_FORM_SIZE  => "Larger than form MAX_FILE_SIZE",
      UPLOAD_ERR_PARTIAL    => "Partial upload.",
      UPLOAD_ERR_NO_FILE    => "No file.",
      UPLOAD_ERR_NO_TMP_DIR => "No temporary directory.",
      UPLOAD_ERR_CANT_WRITE => "Can't write to disk.",
      UPLOAD_ERR_EXTENSION  => "File upload stopped by an extension."
    );

    // Pass in $_FILE(['upload_file']) as an argument
    public function attach_file($file) {
      // Perform error checking on the form parameters
      if (!$file || empty($file) || !is_array($file)) {
        // error: nothing uploaded or wrong argument usage
        $this->errors[] = "No such file was uploaded.";
        return false;
      } elseif ($file['error'] != 0) {
        // error: report what PHP says went wrong
        $this->errors[] = $this->upload_errors[$file['error']];
        return false;
      } else {
        // Set object attributes to the form parameters
        $this->temp_path = $file['tmp_name'];
        $this->filename = basename($file['name']);
        $this->type = $file['type'];
        $this->size = $file['size'];

        // Don't worry about saving anything to the database yet
        return true; 
      }
    }

    public function save() {
      
      // Attempt to move the file
      // Make sure there are no errors
      // Cant save if there are pre-existing errors
      if (!empty($this->errors)) { return false; }

      // Can't save without filename and temp location
      if (empty($this->filename) || empty($this->temp_path)) {
        $this->errors[] = "The file location was not available.";
        return false;
      }

      // Determine the target_path
      $target_path = "c:/xampp/htdocs/inventory_system/assets/" .$this->upload_dir. "/" . $this->filename;

      // Make sure a file doesn't already exist in the target location
      if (file_exists($target_path)) {
        $this->errors[] = "The file {$this->filename} already exists.";
        return false;
      }

      // Attempt to move the file
      if (move_uploaded_file($this->temp_path, $target_path)) { 
        // Success
        // Save a corresponding entry to the database

        // if record already exists
        $check_if_exists = self::find_by_product_id($this->product_id);
        if ($check_if_exists) {
          // Just to update the caption

          $this->update();
          // We are done with temp_path, the file isn't there anymore
          unset($this->temp_path);
          unlink("c:/xampp/htdocs/inventory_system/assets/" .$this->upload_dir. "/" . $check_if_exists->filename. "");
          return true;

        } else {
          $this->create();
          // We are done with temp_path, the file isn't there anymore
          unset($this->temp_path);
          return true;
        }
      } else {
        // File was not moved.
        $this->errors[] = "The file uploaded failed, possibly due to incorrect permissions on the upload folder.";
        return false;
      }
    }
    
    public static function find_by_product_id($id=0) {
      global $database;

      $result_array = self::find_by_sql("SELECT * FROM " . self::$table_name . " WHERE product_id={$id}");
      return !empty($result_array) ? array_shift($result_array) : false;
    }

    public static function inventory_join_image() {
      global $database;

      $query = "SELECT products.product, products.price, products.product_description, product_image.filename ";
      $query .= "FROM products LEFT JOIN product_image ";
      $query .= "ON products.product_id=product_image.product_id";
      return self::find_by_sql($query);
    }

    // Common DatabaseObject methods

    public static function find_all() {
      global $database;

      return self::find_by_sql("SELECT * FROM ".self::$table_name);
    }

    public static function find_by_id($id=0) {
      global $database;

      $result_array = self::find_by_sql("SELECT * FROM " . self::$table_name . " WHERE id={$id}");
      return !empty($result_array) ? array_shift($result_array) : false;
    }

    public static function find_by_sql($sql="") {
      global $database;

      $result_set = $database->query($sql);
      $object_array = array();
      while ($row = $database->fetch_array($result_set)) {
        $object_array[] = self::instantiate($row);
      }
      return $object_array;
    }

    private static function instantiate($record) {
      // Could check that $record exists and is an array
      // Simple long form approach

      $class_name = get_called_class();
      $object = new $class_name;
      // $object->id         = $record['id'];
      // $object->username   = $record['username'];
      // $object->password   = $record['password'];
      // $object->first_name = $record['first_name'];
      // $object->last_name  = $record['last_name'];

      // More dynamic, short-form approach
      foreach ($record as $attribute => $value) {
        if (self::has_attribute($attribute)) {
          $object->$attribute = $value; 
        }
      }
      return $object;
    }

    private static function has_attribute($attribute) {
      // get_object_vars returns an associative array with all attributes
      // (include private ones!) as the keys and their current values as the value
      $class_name = get_called_class();
      $object = new $class_name;
      $object_vars = get_object_vars($object);

      // We don't care about the value, we just want to know if the key exists
      // Will return true of false
      return array_key_exists($attribute, $object_vars);
    }

    protected function attributes() {
      // return an array of attribute keys and their values
      $attributes = array();
      foreach (self::$db_fields as $field) {
        if (property_exists($this, $field)) {
          $attributes[$field] = $this->$field;
        }
      }
      return $attributes;
    }

    protected function sanitized_attributes() {
      global $database;
      $clean_attributes = array();
      // sanitize the values before submitting
      // Note: does not alter the actual value of each attribute
      foreach ($this->attributes() as $key => $value) {
        $clean_attributes[$key] = $database->escape_value($value);
      }
      return $clean_attributes;
    }

    // replace with a custom save()
    // public function save() {
    //   // A new record won't have an id yet.
    //   return isset($this->id) ? $this->update() : $this->create();
    // }

    public function create() {
      global $database;
      // Don't forget your SQL syntax and good habits:
      // - INSERT INTO table (key, key) VALUES ('value','value')
      // - single-quotes around all values
      // - escape all values to prevent sql injection
      $attributes = $this->sanitized_attributes();
      $sql = "INSERT INTO ". self::$table_name ." (";
      $sql .= join(", ", array_keys($attributes));
      $sql .= ") VALUES ('";
      $sql .= join("', '", array_values($attributes));
      $sql .= "')";
      if ($database->query($sql)) {
        $this->id = $database->insert_id();
        return true;
      } else {
        return false;
      }
    }

    public function update() {
      global $database;
      // Dont forget your SQL syntax and good habits
      // - UPDATE table SET key='value', key='value' WHERE condition
      // - single-quotes around all values
      // - escape all values to prevent SQL injection
      $attributes = $this->sanitized_attributes();
      $attribute_pairs = array();
      foreach ($attributes as $key => $value) {
        $attribute_pairs[] = "{$key}='{$value}'";
      }
      $sql = "UPDATE ". self::$table_name ." SET ";
      $sql .= join(", ", $attribute_pairs); 
      $sql .= " WHERE product_id=". $database->escape_value($this->product_id);
      $database->query($sql);
      return ($database->affected_rows() == 1) ? true : false;
    }

    public function delete() {
      global $database;
      // Don't forget SQL syntax and good habits:
      // - DELETE FROM table WHERE condition LIMIT 1
      // - escape all values to prevent SQL injection
      // - use LIMIT 1

      $sql = "DELETE FROM ". self::$table_name ." ";
      $sql .= "WHERE id=". $database->escape_value($this->id);
      $sql .= " LIMIT 1";
      $database->query($sql);
      return ($database->affected_rows() == 1) ? true : false; 
    }

  }

 ?>