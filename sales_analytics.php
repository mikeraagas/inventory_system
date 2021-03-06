<?php require_once('includes/functions.php'); ?>
<?php require_once('includes/session.php'); ?>
<?php require_once('includes/database.php'); ?>
<?php require_once('includes/inventory.php'); ?>
<?php 
  if (!$session->is_logged_in()) {
    redirect_to("index.php");
  }

  // 1. the current page number ($current_page)
  $page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;

  // 2. records per page ($per_page)
  $per_page = 10;

  // Initialize table name
  $table_name = "sales";

  
  if (isset($_POST['submit'])) {

    $keyword = $_POST['keyword'];

    // 3. total record count ($total_count)
    $total_count = Inventory::count_search($keyword, $table_name);

    // Use pagination to find images
    $pagination = new Pagination($page, $per_page, $total_count);

    $query = "SELECT sales.id, sales.product_id, sales.units_sold, sales.sales_date, sales.sales, products.product ";
    $query .= "FROM sales ";
    $query .= "JOIN products ";
    $query .= "ON sales.product_id=products.product_id ";
    // $query .= "ORDER BY sales.id ";
    $query .= "WHERE product LIKE '%" .$keyword. "%' ";
    $query .= "LIMIT {$per_page} ";
    $query .= "OFFSET {$pagination->offset()}";

    $sales = Inventory::find_by_sql($query);
    if (!$sales) {
      $message = "No Records Found.";
    }

  } else {

    // 3. total record count ($total_count)
    $total_count = Inventory::count_all($table_name);

    // Use pagination to find images
    $pagination = new Pagination($page, $per_page, $total_count);

    // Instead of finding all records, just find the records for this page

    $query = "SELECT sales.id, sales.product_id, sales.units_sold, sales.sales_date, sales.sales, products.product ";
    $query .= "FROM sales ";
    $query .= "JOIN products ";
    $query .= "ON sales.product_id=products.product_id ";
    $query .= "ORDER BY sales.id ";
    $query .= "LIMIT {$per_page} ";
    $query .= "OFFSET {$pagination->offset()}"; 
    $sales = Inventory::find_by_sql($query);
      
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
            <li class="active"><a href="sales.php"><i class="icon-tag icon-white"></i>&nbsp;&nbsp;Sales</a></li>
            <li><a href="purchase_order.php"><i class="icon-shopping-cart icon-white"></i>&nbsp;&nbsp;Purchase Order</a></li>
            <li><a href="admin_users.php"><i class="icon-user icon-white"></i>&nbsp;&nbsp;Users</a></li>
          </ul>
        </div><!--/.well -->
      </div><!--/span-->

      <div class="span10">
        <div class="row-fluid content-header">
          <h1>Inventory</h1>
          <ul class="breadcrumb">
            <li><a href="home.php">Home</a> <span class="divider">/</span></li>
            <li><a href="sales.php">Sales</a></li>
          </ul>
        </div> <!-- end of content-header -->

        <div class="row-fluid content-main">
        
          <?php output_message($message); ?>

          <div class="row-fluid">
            <ul class="nav nav-tabs list">
              <li><a href="sales.php">Sales</a></li>
              <li class="active"><a href="sales_analytics.php">Analytics</a></li>
            </ul>
          </div>

          <div id="container" style="width:100%; height:400px;"></div>

        </div> <!-- end of row-container-fluid -->
      
      </div><!-- span -->
    </div><!-- row -->
    
  </div><!--/.fluid-container-->
    
<?php include_once('includes/layouts/footer.php'); ?>
