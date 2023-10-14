<?php
/*
 * Plugin Name: CRUD Operation
 */

// Create the database table on plugin activation
register_activation_hook(__FILE__, 'create_crud_table');

function create_crud_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'crud_table';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Create menu items
add_action('admin_menu', 'my_plugin_menu');

function my_plugin_menu() {
    // Add a top-level menu page
    add_menu_page(
        'Simple CRUD',      // Page title
        'Simple CRUD',      // Menu title
        'manage_options',   // Capability required to access
        'my-plugin',        // Menu slug
        'my_plugin_page'    // Callback function to display the page
    );

    // Add the Student List submenu
    add_submenu_page(
        'my-plugin',           // Parent menu slug
        'Student List',       // Page title
        'Student List',       // Menu title
        'manage_options',      // Capability required to access
        'my-plugin-student-list', // Menu slug
        'my_submenu_page_1'    // Callback function to display the page
    );

    // Add the Student Add submenu
    add_submenu_page(
        'my-plugin',           // Parent menu slug
        'Student Add',        // Page title
        'Student Add',        // Menu title
        'manage_options',      // Capability required to access
        'my-plugin-student-add', // Menu slug
        'my_submenu_page_2'    // Callback function to display the page
    );
}

function my_plugin_page() {
    // Content for the top-level menu page
    echo '<h1>My Plugin</h1>';
}

function my_submenu_page_2() {
    // Content for the Student Add page
    global $wpdb;
    $msg = "";

    if (isset($_POST['btnsubmit'])) {
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_text_field($_POST['email']);
        $wpdb->insert($wpdb->prefix . 'crud_table', array(
            'name' => $name,
            'email' => $email,
        ));
        $msg = "Data saved";
    }
    ?>
    <p><?php echo $msg; ?></p>
    <form action="?page=my-plugin-student-add" method="post">
        <p>
            <label for="name">Name</label>
            <input name="name" type="text">
        </p>
        <p>
            <label for="email">Email</label>
            <input name="email" type="text">
        </p>
        <p>
            <button name="btnsubmit" type="submit">Submit</button>
        </p>
    </form>
    <?php
}

function my_submenu_page_1() {
    // Content for the Student List page
    global $wpdb;
    $table_name = $wpdb->prefix . 'crud_table';
    $alldata = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    ?>

    <table>
        <tr>
            <th>Sr No</th>
            <th>Name</th>
            <th>Email</th>
            <th>Action</th>
        </tr>
        <?php
        if ($alldata) {
            foreach ($alldata as $row) {
                ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td>
                        <a href="?page=my-plugin-student-list&action=edit&id=<?php echo $row['id']; ?>">Edit</a>
                        <a href="?page=my-plugin-student-list&action=delete&id=<?php echo $row['id']; ?>">Delete</a>
                    </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="4">No data found.</td>
            </tr>
            <?php
        }
        ?>
    </table>
    <?php
    // Check if an action is requested (Edit or Delete)
    if (isset($_GET['action'])) {
        if ($_GET['action'] === 'edit') {
            // Handle edit action
            $id = intval($_GET['id']);
            $record = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);

            if ($record) {
                // Display a form for editing the record
                ?>
                <h2>Edit Student</h2>
                <form action="?page=my-plugin-student-list&action=update&id=<?php echo $id; ?>" method="post">
                    <p>
                        <label for="name">Name</label>
                        <input name="name" type="text" value="<?php echo $record['name']; ?>">
                    </p>
                    <p>
                        <label for="email">Email</label>
                        <input name="email" type="text" value="<?php echo $record['email']; ?>">
                    </p>
                    <p>
                        <button name="btnupdate" type="submit">Update</button>
                    </p>
                </form>
                <?php
            }
        } elseif ($_GET['action'] === 'delete') {
            // Handle delete action
            $id = intval($_GET['id']);
            $wpdb->delete($table_name, array('id' => $id));
            echo "<p>Record with ID $id has been deleted.</p>";
        } elseif ($_GET['action'] === 'update') {
            // Handle update action
            $id = intval($_GET['id']);
            $name = sanitize_text_field($_POST['name']);
            $email = sanitize_text_field($_POST['email']);

            $wpdb->update($table_name, array('name' => $name, 'email' => $email), array('id' => $id));

            echo "<p>Record with ID $id has been updated.</p>";
        }
    }
}

