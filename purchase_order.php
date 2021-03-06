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
  $table_name = "purchase";

  
  if (isset($_POST['submit'])) {

    $keyword = $_POST['keyword'];

    // 3. total record count ($total_count)
    $total_count = Inventory::count_search($keyword, $table_name);

    // Use pagination to find images
    $pagination = new Pagination($page, $per_page, $total_count);

    $query = "SELECT purchase.id, purchase.product_id, purchase.units_purchase, purchase.purchase_date, products.product ";
    $query .= "FROM purchase ";
    $query .= "JOIN products ";
    $query .= "ON purchase.product_id=products.product_id ";
    // $query .= "ORDER BY purchase.id ";
    $query .= "WHERE product LIKE '%" .$keyword. "%' ";
    $query .= "LIMIT {$per_page} ";
    $query .= "OFFSET {$pagination->offset()}";

    $purchase = Inventory::find_by_sql($query);
    if (!$purchase) {
      $message = "No Records Found.";
    }

  } else {

    // 3. total record count ($total_count)
    $total_count = Inventory::count_all($table_name);

    // Use pagination to find images
    $pagination = new Pagination($page, $per_page, $total_count);

    // Instead of finding all records, just find the records for this page

    $query = "SELECT purchase.id, purchase.product_id, purchase.units_purchase, purchase.purchase_date, products.product ";
    $query .= "FROM purchase ";
    $query .= "JOIN products ";
    $query .= "ON purchase.product_id=products.product_id ";
    $query .= "ORDER BY purchase.id ";
    $query .= "LIMIT {$per_page} ";
    $query .= "OFFSET {$pagination->offset()}";
    $purchase = Inventory::find_by_sql($query);
      
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
            <li><a href="sales.php"><i class="icon-tag icon-white"></i>&nbsp;&nbsp;Sales</a></li>
            <li class="active"><a href="purchase_order.php"><i class="icon-shopping-cart icon-white"></i>&nbsp;&nbsp;Purchase Order</a></li>
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
             <div class="span8">
               <ul class="nav nav-tabs list">
                <li class="active"><a href="sales.php">Purchase Orders</a></li>
                <li><a href="purchase_analytics.php">Analytics</a></li>
              </ul>
             </div>

             <div class="span3">
               <form action="sales.php" method="post" class="form-search list">
                <div class="input-append">
                  <input type="text" name="keyword" class="input-medium search-query">
                  <input type="submit" name="submit" class="btn btn-primary" value="Search">
                </div>
              </form>
             </div>
           </div>


          <table class="table table-striped table-bordered table-hover inventory-table">
            <thead class="btn-success">
              <th>ID</th>
              <th>Date</th>
              <th>Product</th>
              <th>Purchase Order</th>
              <th>Actions</th>
            </thead>
            <tbody>
              <?php 
                foreach ($purchase as $product_purchase) {
                  echo "<tr><td>"; 
                  echo $product_purchase->id;
                  echo "</td><td>";
                  echo $product_purchase->purchase_date;
                  echo "</td><td>";
                  echo $product_purchase->product;
                  echo "</td><td>";
                  echo $product_purchase->units_purchase;
                  echo "</td><td>";
                  echo "<a href='delete_product.php?id=". $product_purchase->product_id ."' class='btn tooltip_dialog' data-toggle='tooltip' data-placement='left' title='Delete Record' onclick='return confirmAction()'><i class='icon-trash'></i></a>";
                  echo "</td></tr>";
                }
                
               ?>
            </tbody>
          </table>

          <a href="purchase_order.php" class="btn btn-large left tooltip_dialog" data-toggle="tooltip" data-placement="right" title="Refresh Inventory List"><i class="icon-refresh"></i></a>

          <div class="pagination pagination-right">
            <ul>
              <?php 
                if ($pagination->total_pages() > 1) {
                  
                  if ($pagination->has_previous_page()) {
                    echo "<li><a href=\"purchase_order.php?page=";
                    echo $pagination->previous_page();
                    echo "\">Previous</a></li>";
                  }

                  for ($i=1; $i <= $pagination->total_pages(); $i++) { 
                    if ($i == $page) {
                      echo "<li class='active'><span>{$i}</span></li>";
                    } else {
                      echo "<li><a href=\"purchase_order.php?page={$i}\">{$i}</a></li>";
                    }
                  }

                  if ($pagination->has_next_page()) {
                    echo "<li><a href=\"purchase_order.php?page=";
                    echo $pagination->next_page();
                    echo "\">Next</a></li>";
                  }
                }
               ?>
            </ul>
          </div>

        </div> <!-- end of row-container-fluid -->
      
      </div><!-- span -->
    </div><!-- row -->
    
  </div><!--/.fluid-container-->
    
<?php include_once('includes/layouts/footer.php'); ?>
