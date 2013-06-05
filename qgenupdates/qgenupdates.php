<?php
/*
Plugin Name: QGen Updates
Plugin URI: http://qsp.su
Description: Плагин позволяет добавлять описания для генератора файла обновлений QGen. 
Author: rrockru
Author URI: http://rrock.nx0.ru
Version: 1.0
*/

// Hook for adding admin menus
add_action('admin_menu', 'qgen_add_pages');

// action function for above hook
function qgen_add_pages() {
    add_management_page('Обновления QGen', 'Обновления QGen', 8, 'qgenupdates', 'mt_manage_page');
}

// mt_manage_page() displays the page content for the Test Manage submenu
function mt_manage_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . "qgenupdates";    

    echo '<div class="wrap">';
    
    if ( $_GET['action'] == "addedit")
    {
      $updid = $_GET['id']; 
      $update = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $updid");

      if ($_POST['save'] == "Y") {
        $wrongid = $wpdb->get_var(
          $wpdb->prepare(
            "
            SELECT id 
            FROM $table_name 
            WHERE version = %s",
            $_POST['version']
          )
        );
        if (($wrongid <= 0) || ($_POST['version'] == $update->version)) {
            if ($updid == "") {
                $wpdb->insert( 
                  $table_name, 
                  array( 
                    'time' => current_time('mysql'), 
                    'version' => $_POST['version'], 
                    'description' => $_POST['description'] 
                  ) 
                );
            } else {
                $wpdb->update( 
                  $table_name, 
                  array( 
                    'version' => $_POST['version'], 
                    'description' => $_POST['description'] 
                  ), 
                  array( 'id' => $updid ), 
                  array( 
                    '%s',
                    '%s'
                  ), 
                  array( '%d' ) 
                );
            }
            unset($_POST['save']);
            $wrongid = 0;
          }

          if ($updid == "") $updid = $wpdb->insert_id;

          if ($wrongid <= 0)
            $update = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $updid");
          else
          {
            $update->version = $_POST['version'];
            $update->description = $_POST['description'];
          }
        }        
?>
<h2>
  <?
  if ($updid != "")
    echo "Правка";
  else
    echo "Добавление";
  ?>
</h2>
<div id="addedit">
    <form method="post" action="<?php echo admin_url( 'tools.php?page=qgenupdates&action=addedit&id='.$updid); ?>">
      <input type="hidden" name="save" value="Y">
      <p>Версия:
        <?php if($wrongid > 0) echo "<font color='red'>Такая версия уже существует!</font>"; ?>
      </p>
      <input type="text" name="version" maxlength="5" size="58" value="<?php echo $update->version; ?>">
      <p>Описание:</p>
      <textarea name="description" cols="60" rows="15"><?php echo $update->description; ?></textarea><br/>
      <input type="submit" value="Сохранить">
      <a href="<?php echo admin_url( 'tools.php?page=qgenupdates'); ?>">Назад</a>
    </form>
</div>
<?
    } else {
      if ($_GET['action'] == "remove") {
        $wpdb->query( 
          $wpdb->prepare( 
            "
              DELETE FROM $table_name
              WHERE id = %d
            ",
            $_GET['id']
            )
        );
      }
      $updates = $wpdb->get_results("SELECT * FROM $table_name ORDER BY version DESC" );
?>
<h2>Обновления QGen
<a class="add-new-h2" href="<?php echo admin_url( 'tools.php?page=qgenupdates&action=addedit'); ?>">Добавить новое</a>
</h2>
<table class="widefat fixed" cellspacing="0">
    <thead>
    <tr>
        <th width="5%">ID</th>
        <th width="10%">Версия</th>
        <th>Описание</th>
    </tr>    
    <thead>
    <tbody>
<?
    foreach ( $updates as $update ) 
    {
?>
        <tr>
          <td><?php echo $update->id; ?></td>
          <td><?php echo $update->version; ?></td>
          <td>
            <?php echo $update->description; ?>
            <div class="row-actions">
              <span><a href="<?php echo admin_url( 'tools.php?page=qgenupdates&action=addedit&id='.$update->id); ?>">изменить</a> |</span>
              <span><font color="red"><a href="<?php echo admin_url( 'tools.php?page=qgenupdates&action=remove&id='.$update->id); ?>">удалить</a></font></span>
            </div>      
          </td>
        </tr>
<?
    }
?>
    </tbody>    
</table>
</div>
<?
}
}

global $qgen_db_version;
$qgen_db_version = "1.0";

function qgen_install() {
    global $wpdb;
    global $qgen_db_version;

    $table_name = $wpdb->prefix . "qgenupdates";   

    $installed_ver = get_option( "qgen_db_version" );

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        version tinytext NOT NULL,
        description text NOT NULL,
        UNIQUE KEY id (id)
    );";

   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
   dbDelta( $sql );
 
   add_option( "qgen_db_version", $qgen_db_version );

    if( $installed_ver != $qgen_db_version ) {

      $sql = "CREATE TABLE $table_name (
         id mediumint(9) NOT NULL AUTO_INCREMENT,
         time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
         version tinytext NOT NULL,
         description text NOT NULL,
         UNIQUE KEY id (id)
      );";

      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta( $sql );

      update_option( "qgen_db_version", $qgen_db_version );
    }
}

function qgen_install_data() {
   global $wpdb;
   $table_name = $wpdb->prefix . "qgenupdates"; 

   $rows_affected = $wpdb->insert( $table_name, array( 'time' => current_time('mysql'), 'version' => "5.0.0", 'description' => "Новая версия редактора игр для платформы QSP." ) );
}

function qgen_update_db_check() {
    global $qgen_db_version;
    if (get_site_option( 'qgen_db_version' ) != $qgen_db_version) {
        qgen_install();
    }
}
add_action( 'plugins_loaded', 'qgen_update_db_check' );

function qgen_uninstall() {
    global $wpdb;

    $table_name = $wpdb->prefix . "qgenupdates";   
      
    $wpdb->query( "DROP TABLE $table_name;" );
}

register_activation_hook( __FILE__, 'qgen_install' );
register_activation_hook( __FILE__, 'qgen_install_data' );

register_uninstall_hook( __FILE__, 'qgen_uninstall' );

?>