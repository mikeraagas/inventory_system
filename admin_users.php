<?php require_once('includes/functions.php'); ?>
<?php require_once('includes/session.php'); ?>
<?php require_once('includes/database.php'); ?>
<?php require_once('includes/databaseobject.php'); ?>
<?php require_once('includes/user.php'); ?>
<?php 
  if (!$session->is_logged_in()) {
    redirect_to("index.php");
  }

  // 1. the current page number ($current_page)
  $page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;

  // 2. records per page ($per_page)
  $per_page = 10;

  if (isset($_POST['submit'])) {

    $keyword = $_POST['keyword'];

    // 3. total record count ($total_count)
    $total_count = User::count_search($keyword);

    // Use pagination to find images
    $pagination = new Pagination($page, $per_page, $total_count);

    $sql = "SELECT * FROM users ";
    $sql .= "WHERE first_name LIKE '%" .$keyword. "%' ";
    $sql .= "OR last_name LIKE '%" .$keyword. "%' ";
    $sql .= "LIMIT {$per_page} ";
    $sql .= "OFFSET {$pagination->offset()}";

    $users = User::find_by_sql($sql);
    if (!$users) {
      $message = "No Records Found.";
    }

  } else {

    // 3. total record count ($total_count)
    $total_count = User::count_all();

    // Use pagination to find images
    $pagination = new Pagination($page, $per_page, $total_count);

    // Instead of finding all records, just find the records for this page

    $query = "SELECT * FROM users ";
    $query .= "LIMIT {$per_page} ";
    $query .= "OFFSET {$pagination->offset()}";
    $users = User::find_by_sql($query);
      
  }


 ?>

<?php include_once('includes/layouts/header.php'); ?>
 
  <div class="container-fluid">
    <div class="row-fluid">
      <div class="span2">
        <div class="well sidebar-nav">
          <ul class="nav nav-tabs nav-stacked">
            <li><a href="home.php"><i class="icon-home icon-white"></i>&nbsp;&nbsp;Home</a></li>
            <li><a href="inventory.php"><i class="icon-list icon-white"></i>&nbsp;&nbsp;Inventory</a></li>
            <li><a href="products.php"><i class="icon-barcode icon-white"></i>&nbsp;&nbsp;Products</a></li>
            <li><a href="#"><i class="icon-tag icon-white"></i>&nbsp;&nbsp;Sales</a></li>
            <li><a href="#"><i class="icon-shopping-cart icon-white"></i>&nbsp;&nbsp;Orders</a></li>
            <li class="active"><a href="#"><i class="icon-user icon-white"></i>&nbsp;&nbsp;Users</a></li>
          </ul>
        </div><!--/.well -->
      </div><!--/span-->

      <div class="span10">
        <div class="row-fluid content-header">
          <h1>Users</h1>
          <ul class="breadcrumb">
            <li><a href="home.php">Home</a> <span class="divider">/</span></li>
            <li><a href="admin_users.php">Users</a></li>
          </ul>
        </div> <!-- end of content-header -->

        <div class="row-fluid content-main">

          <?php output_message($message); ?>

          <div class="row-fluid">
            <div class="span8">
              <ul class="nav nav-tabs">
                <li class="active"><a href="admin_users.php">Admin Users</a></li>
                <li><a href="create_user.php">Add User</a></li>
              </ul>
            </div>

            <div class="span3">
              <form action="admin_users.php" method="post" class="form-search list">
                <div class="input-append">
                  <input type="text" name="keyword" class="input-medium search-query">
                  <input type="submit" name="submit" class="btn btn-primary" value="Search">
                </div>
              </form>
            </div>
          </div> <!-- end of row-fluid -->

          <table class="table table-striped table-bordered user-table">
            <thead class="btn-success">
              <th>ID</th>
              <th>Username</th>
              <th>Full Name</th>
              <th>Email</th>
              <th>Contact Number</th>
              <th>Actions</th>
            </thead>
            <tbody>
              <?php 
                foreach ($users as $user) {
                  echo "<tr><td>";
                  echo $user->id;
                  echo "</td><td>";
                  echo $user->username;
                  echo "</td><td>";
                  echo $user->full_name();
                  echo "</td><td>"; 
                  echo $user->email;
                  echo "</td><td>";
                  echo $user->contact_number;
                  echo "</td><td>";
                  echo "<div class='btn-group'>";
                  echo "<a href='#' class='btn'><i class='icon-eye-open'></i></a>";
                  echo "<a href='#' class='btn'><i class='icon-pencil'></i></a>";
                  echo "<a href='#' class='btn'><i class='icon-trash'></i></a>";
                  echo "</div>";
                  echo "</td></tr>";
                }
                
               ?>
            </tbody>
          </table>

          <a href="admin_users.php" class="btn btn-large left tooltip_dialog" data-toggle="tooltip" data-placement="right" title="Refresh Admin Users List"><i class="icon-refresh"></i></a>

          <div class="pagination pagination-right">
            <ul>
              <?php 
                if ($pagination->total_pages() > 1) {
                  
                  if ($pagination->has_previous_page()) {
                    echo "<li><a href=\"admin_users.php?page=";
                    echo $pagination->previous_page();
                    echo "\">Previous</a></li>";
                  }

                  for ($i=1; $i <= $pagination->total_pages(); $i++) { 
                    if ($i == $page) {
                      echo "<li class='active'><span>{$i}</span></li>";
                    } else {
                      echo "<li><a href=\"admin_users.php?page={$i}\">{$i}</a></li>";
                    }
                  }

                  if ($pagination->has_next_page()) {
                    echo "<li><a href=\"admin_users.php?page=";
                    echo $pagination->next_page();
                    echo "\">Next</a></li>";
                  }
                }
               ?>
            </ul>
          </div>

        </div> <!-- end of row-fluid -->
        
      </div><!--/span-->
    </div><!--/row-->

  </div><!--/.fluid-container-->

<?php include_once('includes/layouts/footer.php'); ?>