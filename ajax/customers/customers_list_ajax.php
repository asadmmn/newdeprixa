<?php
// *************************************************************************
// *                                                                       *
// * DEPRIXA PRO -  Integrated Web Shipping System                         *
// * Copyright (c) JAOMWEB. All Rights Reserved                            *
// *                                                                       *
// *************************************************************************
// *                                                                       *
// * Email: support@jaom.info                                              *
// * Website: http://www.jaom.info                                         *
// *                                                                       *
// *************************************************************************
// *                                                                       *
// * This software is furnished under a license and may be used and copied *
// * only  in  accordance  with  the  terms  of such  license and with the *
// * inclusion of the above copyright notice.                              *
// * If you Purchased from Codecanyon, Please read the full License from   *
// * here- http://codecanyon.net/licenses/standard                         *
// *                                                                       *
// *************************************************************************

require_once("../../loader.php");
// include ('../../lib/User.php');

$db = new Conexion;
$user = new User;
$userData = $user->cdp_getUserData();

$search = isset($_REQUEST['search']) ? cdp_sanitize($_REQUEST['search']) : null;

$tables = "cdb_users u 
           LEFT JOIN cdb_countries c 
           ON LEFT(u.phone, LENGTH(c.phone_code)) = c.phone_code";

$fields = "u.*, 
           CONCAT(u.fname, ' ', u.lname) as name,
           DATE_FORMAT(u.created, '%d. %b. %Y %H:%i') as cdate,
           DATE_FORMAT(u.lastlogin, '%d. %b. %Y %H:%i') as adate,
           c.name as country_name";

$currentUserPhone = $userData->phone;
$currentUserCountryCode = substr($currentUserPhone, 0, 3); // Assuming country code is the first 3 digits
$currentUserLevel = $userData->userlevel;

$sWhere = "u.userlevel = 1";

if ($currentUserLevel != 9) {
    $sWhere .= " AND LEFT(u.phone, 3) = '$currentUserCountryCode'";
}

$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
$per_page = 10;
$adjacents = 4;
$offset = ($page - 1) * $per_page;

$sql = "SELECT $fields FROM $tables WHERE $sWhere";

$query_count = $db->cdp_query($sql);
$db->cdp_execute();
$numrows = $db->cdp_rowCount();

$db->cdp_query($sql . " LIMIT $offset, $per_page");
$data = $db->cdp_registros();

$total_pages = ceil($numrows / $per_page);

if ($numrows > 0) { ?>
    <table id="file_export" class="table border table-striped table-bordered display text-nowrap dataTable" aria-describedby="file_export_info">
        <thead>
            <tr>
                <th><b><?php echo $lang['edit-clien38'] ?></b></th>
                <th class="text-center"><b><?php echo $lang['edit-clien39'] ?></b></th>
                <th class="text-center"><b><?php echo $lang['user-account21000'] ?></b></th>
                <?php if ($currentUserLevel == 9) { ?>
                    <th class="text-center">Country</th>
                <?php } ?>
                <th class="text-center"><b><?php echo $lang['edit-clien40'] ?></b></th>
                <th class="text-center"><b><?php echo $lang['edit-clien41'] ?></b></th>
                <th class="text-center"><b><?php echo $lang['edit-clien42'] ?></b></th>
                <th class="text-center"><b><?php echo $lang['edit-clien43'] ?></b></th>
            </tr>
        </thead>
        <tbody>
		<?php foreach ($data as $user) { ?>
    <tr>
        <td class="text-center"><?php echo htmlspecialchars($user->fname . ' ' . $user->lname, ENT_QUOTES, 'UTF-8'); ?></td>
        <td class="text-center"><?php echo htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8'); ?></td>
        <td class="text-center"><?php echo htmlspecialchars($user->locker, ENT_QUOTES, 'UTF-8'); ?></td>

        <?php if ($currentUserLevel == 9) { ?>
            <td class="text-center">
                <?php
                // Fetch country for the user
                $userid = $user->id;

                $db->cdp_query('  SELECT c.name AS country_name 
    FROM cdb_senders_addresses sa
    INNER JOIN cdb_countries c ON sa.country = c.id
    WHERE sa.user_id = :id');
                $db->bind(':id', $userid);
                $db->cdp_execute();
                $countryData = $db->cdp_registros();


foreach ($countryData as $country) {
	
                echo $country->country_name;
    
}
	?>
            </td>
        <?php } ?>

                    <td class="text-center"><?php echo cdp_userStatus($user->active, $user->id, $lang); ?></td>
                    <td class="text-center"><?php echo cdp_isAdmin($user->userlevel, $lang); ?></td>
                    <td class="text-center"><?php echo ($user->adate) ? $user->adate : "-/-"; ?></td>
                    <td align="center">
                        <a href="customers_edit.php?user=<?php echo $user->id; ?>" data-toggle="tooltip" data-placement="top" title="<?php echo $lang['edit-clien46']; ?>">
                            <i class="ti-pencil"></i>
                        </a>
                        <a href="newsletter.php?email=<?php echo $user->email; ?>" data-toggle="tooltip" data-placement="top" title="<?php echo $lang['edit-clien45']; ?>">
                            <i style="color:#F5590D" class="ti-email"></i>
                        </a>
                        <?php if ($user->id != 1 && $userData->userlevel == 9) { ?>
                            <a onclick="cdp_eliminar('<?php echo $user->id; ?>')" id="item_<?php echo $user->id; ?>" class="delete" data-toggle="tooltip" data-placement="top" title="<?php echo $lang['edit-clien47']; ?>">
                                <div class="icon-holder"><i class="fi fi-rr-trash"></i></div>
                            </a>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="pull-right">
        <?php echo cdp_paginate($page, $total_pages, $adjacents, $lang); ?>
    </div>
<?php } else { ?>
    <tr>
        <td colspan="8" class="text-center">
            <img src="assets/images/alert/ohh_shipment.png" width="150" />
            <p>No data found</p>
        </td>
    </tr>
<?php } ?>
