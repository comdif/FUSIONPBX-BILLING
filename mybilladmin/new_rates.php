<?php
####  2023-2025 Billing software - Christian Zeler@Comdif Innovation
require("header.php");
session_start();

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
function csrf_token_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '" />';
}
function check_csrf() {
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('<div class="error">Invalid CSRF token</div>');
    }
}
function h($str) { return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

// Input validation helpers
function get_post($key, $default = '') {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}
function get_get($key, $default = '') {
    return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$errors = [];

// Add New Rate
if ($action === "add") {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && get_post('button') === 'store') {
        check_csrf();
        $pat   = get_post('pat');
        $com   = get_post('com');
        $cca   = get_post('cco');
        $inc   = get_post('inc');
        $eka   = get_post('eka');
        $costa = get_post('costa');

        // Basic validation
        if ($pat === '' || $com === '' || !is_numeric($eka) || !is_numeric($costa)) {
            $errors[] = "Please fill all required fields correctly.";
        }
        if (empty($errors)) {
            $sql = "INSERT INTO v_routes (pattern, comment, trunks, connectcost, includedseconds, ek, cost, custom1, custom2, custom3)
                    VALUES ($1, $2, '', $3, $4, $5, $6, '0', '0', '0')";
            $res = pg_prepare($dbcon, "new_rate_add", $sql);
            $result = pg_execute($dbcon, "new_rate_add", [$pat, $com, $cca, $inc, $eka, $costa]);
            if ($result) {
                echo '<center>OK Done</center>';
                echo "<script>window.location.replace('" . h($_SERVER['PHP_SELF']) . "?action=add&pat=" . urlencode($pat) . "')</script>";
                require("footer.php");
                exit;
            } else {
                $errors[] = "Database error. Please check logs.";
                error_log("DB error on add: " . pg_last_error($dbcon));
            }
        }
    }
    ?>
    <div class="headline_global">Rates and routing</div><br />
    <p align="center"><font color="#CC0000"><strong>Prices are in cents/100, eg for 11 cents enter 1100<br>Never use comma!</strong></font></p>
    <?php
    if ($errors) {
        echo '<div class="error" align="center">' . implode('<br>', array_map('h', $errors)) . '</div>';
    }
    ?>
    <form name="new_rates_frm" action="<?=h($_SERVER['PHP_SELF'])?>?action=add" method="POST" autocomplete="off">
        <?= csrf_token_field() ?>
        <input type="hidden" name="button" value="store" />
        <table class="rates_tbl" align="center">
            <tr><td>Prefix*</td><td><input type="text" name="pat" value="<?=h(get_post('pat'))?>" required /></td></tr>
            <tr><td>Destination*</td><td><input type="text" name="com" value="<?=h(get_post('com'))?>" required /></td></tr>
            <tr><td>Purchase*</td><td><input type="number" name="eka" value="<?=h(get_post('eka'))?>" required min="0" /></td></tr>
            <tr><td>Sale*</td><td><input type="number" name="costa" value="<?=h(get_post('costa'))?>" required min="0" /></td></tr>
            <tr><td>Connect cost</td><td><input type="number" name="cco" value="<?=h(get_post('cco'))?>" min="0" /></td></tr>
            <tr><td>Included seconds</td><td><input type="number" name="inc" value="<?=h(get_post('inc'))?>" min="0" /></td></tr>
            <tr><td class="gapright" colspan="2"><input type="submit" value="Create" /></td></tr>
        </table>
    </form>
    <div align='center'>Return: <a class='big_links' href='<?=h($_SERVER['PHP_SELF'])?>'>Rate list</a></div>
    <?php
    require("footer.php");
    exit;
}

// Edit Rate
if ($action === "edit") {
    $iPattern = get_get('pat');
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && get_post('button') === 'store') {
        check_csrf();
        $pat   = get_post('pat');
        $com   = get_post('com');
        $cca   = get_post('cco');
        $inc   = get_post('inc');
        $eka   = get_post('eka');
        $costa = get_post('costa');
        if ($pat === '' || $com === '' || !is_numeric($eka) || !is_numeric($costa)) {
            $errors[] = "Please fill all required fields correctly.";
        }
        if (empty($errors)) {
            $sql = "UPDATE v_routes SET pattern=$1, trunks='', comment=$2, ek=$3, cost=$4, connectcost=$5, includedseconds=$6
                    WHERE CAST(pattern AS TEXT)=$7";
            pg_prepare($dbcon, "edit_rate_upd", $sql);
            $result = pg_execute($dbcon, "edit_rate_upd", [$pat, $com, $eka, $costa, $cca, $inc, $pat]);
            if ($result) {
                echo '<center>OK Done</center>';
                echo "<script>window.location.replace('" . h($_SERVER['PHP_SELF']) . "?action=edit&pat=" . urlencode($pat) . "')</script>";
                require("footer.php");
                exit;
            } else {
                $errors[] = "Database error. Please check logs.";
                error_log("DB error on edit: " . pg_last_error($dbcon));
            }
        }
    }
    // Fetch current rate for editing
    $sql = "SELECT * FROM v_routes WHERE CAST(pattern AS TEXT)=$1 LIMIT 1";
    pg_prepare($dbcon, "edit_rate_sel", $sql);
    $aRate = pg_execute($dbcon, "edit_rate_sel", [$iPattern]);
    if ($rowa = pg_fetch_assoc($aRate)) {
        ?>
        <div class="headline_global">Rates and routing</div><br>
        <p align="center">
            <font color="#CC0000"><strong>Prices are in cents/100, eg for 11 cents enter 1100<br>Never use comma!</strong></font><br>
            <font color="#330000" >
                Actual price for this destination <?= h($rowa['pattern']) ?> - <?= h($rowa['comment']) ?> is:<br>
                Purchase: <strong style="color:#0000FF"><?= h($rowa['ek']/100) ?></strong> Cents,
                Retail: <strong style="color:#0000FF"><?= h($rowa['cost']/100) ?></strong> Cents,
                Connect cost: <strong style="color:#0000FF"><?= h($rowa['connectcost']/100) ?></strong> Cents,
                Included seconds: <strong style="color:#0000FF"><?= h($rowa['includedseconds']) ?></strong> Seconds
            </font>
        </p>
        <?php
        if ($errors) {
            echo '<div class="error" align="center">' . implode('<br>', array_map('h', $errors)) . '</div>';
        }
        ?>
        <form name="edit_rates_frm" action="<?=h($_SERVER['PHP_SELF'])?>?action=edit&pat=<?=urlencode($iPattern)?>" method="POST">
            <?= csrf_token_field() ?>
            <input type="hidden" name="button" value="store" />
            <table class="rates_tbl" align="center">
                <tr><td>Prefix*</td><td><input type="text" name="pat" value="<?=h($rowa['pattern'])?>" required /></td></tr>
                <tr><td>Destination*</td><td><input type="text" name="com" value="<?=h($rowa['comment'])?>" required /></td></tr>
                <tr><td>Purchase*</td><td><input type="number" name="eka" value="<?=h($rowa['ek'])?>" required min="0" /></td></tr>
                <tr><td>Sale*</td><td><input type="number" name="costa" value="<?=h($rowa['cost'])?>" required min="0" /></td></tr>
                <tr><td>Connect cost</td><td><input type="number" name="cco" value="<?=h($rowa['connectcost'])?>" min="0" /></td></tr>
                <tr><td>Included seconds</td><td><input type="number" name="inc" value="<?=h($rowa['includedseconds'])?>" min="0" /></td></tr>
                <tr><td class="gapright" colspan="2"><input type="submit" value="Change" /></td></tr>
            </table>
        </form>
        <div align='center'>Return: <a class='big_links' href='<?=h($_SERVER['PHP_SELF'])?>'>Rate list</a></div>
        <?php
    } else {
        echo '<div class="error">Rate not found.</div>';
    }
    require("footer.php");
    exit;
}

// Delete Rate
if ($action === "del") {
    $pat = get_get('pat');
    if ($pat !== '') {
        $sql = "DELETE FROM v_routes WHERE CAST(pattern AS TEXT)=$1";
        pg_prepare($dbcon, "del_rate", $sql);
        $res = pg_execute($dbcon, "del_rate", [$pat]);
        if ($res) {
            echo "<div align='center'>OK " . h($pat) . " deleted<br />Return: <a class='big_links' href='" . h($_SERVER['PHP_SELF']) . "?letter=" . urlencode(get_get('let')) . "'>Link</a></div>";
        } else {
            echo "<div class='error'>Could not delete. Database error.</div>";
            error_log("DB error on delete: " . pg_last_error($dbcon));
        }
    }
    require("footer.php");
    exit;
}

// Default: Show Rate List
$letter = get_get('letter');
?>
<br>
<div align="center">
    <form name="Myselect" action="<?=h($_SERVER['PHP_SELF'])?>" method="get" style="display:inline;">
        Name/Prefix <input type="text" name="letter" value="<?=h($letter)?>" />
        <input type="submit" value="Search">
    </form>
    <font size="1"> (Prepend with * for a strict search)</font>
</div>
<br>
<?php
$sCdrsql = "SELECT comment,trunks,ek,cost,connectcost,includedseconds,pattern FROM v_routes";
$searchActive = false;
if ($letter !== '') {
    $searchActive = true;
    if ($letter[0] === '*') {
        $search = substr($letter, 1);
        $sCdrsql .= " WHERE comment=$$" . " OR CAST(pattern AS TEXT)=$$" . " ORDER BY comment";
        $params = [$search, $search];
    } else {
        $sCdrsql .= " WHERE comment ILIKE $$" . " OR CAST(pattern AS TEXT) LIKE $$" . " ORDER BY comment";
        $params = [$letter . '%', $letter . '%'];
    }
    ?>
    <table class="rgrey" align="center">
        <tr>
            <td>
                Below Destinations in free package?
                <form style="display:inline;" name="freedest" action="<?=h($_SERVER['PHP_SELF'])?>?letter=<?=h($letter)?>" method="POST">
                    <?= csrf_token_field() ?>
                    <input type="hidden" name="action" value="freedest" />
                    <input type="hidden" name="leti" value="<?=h($letter)?>" />
                    <input type="submit" value="Include" />
                </form>
                &nbsp;|&nbsp;
                <form style="display:inline;" name="delfreedest" action="<?=h($_SERVER['PHP_SELF'])?>?letter=<?=h($letter)?>" method="POST">
                    <?= csrf_token_field() ?>
                    <input type="hidden" name="action" value="freedest" />
                    <input type="hidden" name="remfree" value="remfree" />
                    <input type="hidden" name="leti" value="<?=h($letter)?>" />
                    <input type="submit" value="Remove" />
                </form>
            </td>
        </tr>
    </table>
    <?php
} else {
    echo '<strong>' . h(translate("srate")) . '</strong>';
    require("footer.php");
    exit;
}

// Handle "free destinations" logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && get_post('action') === 'freedest') {
    check_csrf();
    $leti = get_post('leti');
    if (!empty(get_post('remfree'))) {
        // Remove from free package
        if ($leti[0] === '*') {
            $sql = "DELETE FROM v_free WHERE comment=$1 OR CAST(pattern AS TEXT)=$1";
            pg_prepare($dbcon, "del_freedest", $sql);
            pg_execute($dbcon, "del_freedest", [substr($leti, 1)]);
        } else {
            $sql = "DELETE FROM v_free WHERE comment ILIKE $1 OR CAST(pattern AS TEXT) LIKE $1";
            pg_prepare($dbcon, "del_freedest_like", $sql);
            pg_execute($dbcon, "del_freedest_like", [$leti . '%']);
        }
    } else {
        // Add to free package
        if ($leti[0] === '*') {
            $sql = "SELECT pattern,comment FROM v_routes WHERE comment=$1 OR CAST(pattern AS TEXT)=$1";
            $params = [substr($leti, 1)];
        } else {
            $sql = "SELECT pattern,comment FROM v_routes WHERE comment ILIKE $1 OR CAST(pattern AS TEXT) LIKE $1";
            $params = [$leti . '%'];
        }
        $sea = pg_query_params($dbcon, $sql, $params);
        while ($pop = pg_fetch_assoc($sea)) {
            $insql = "INSERT INTO v_free (pattern,comment) VALUES ($1,$2) ON CONFLICT (pattern) DO NOTHING";
            pg_prepare($dbcon, "ins_freedest", $insql);
            pg_execute($dbcon, "ins_freedest", [$pop['pattern'], $pop['comment']]);
        }
    }
    echo "<script>window.location.replace('" . h($_SERVER['PHP_SELF']) . "?letter=" . urlencode($leti) . "')</script>";
    require("footer.php");
    exit;
}

// Show rates table
echo '<div class="headline_global">Prices & Destinations</div>
<table class="rgrey" align="center">
    <tr>
        <th class="small_headline">Destination</th>
        <th class="small_headline">Trunk</th>
        <th class="small_headline">Prefix</th>
        <th class="small_headline">Purchase</th>
        <th class="small_headline">Sale</th>
        <th class="small_headline">Connect cost</th>
        <th class="small_headline">Included time</th>
        <th class="small_headline"></th>
        <th class="small_headline"></th>
        <th class="small_headline"></th>
    </tr>';

if (isset($params)) {
    $uno = pg_query_params($dbcon, $sCdrsql, $params);
} else {
    $uno = pg_query($dbcon, $sCdrsql);
}
while ($res = pg_fetch_assoc($uno)) {
    $iRate = number_format($res['cost']/100, 1, ",", ".");
    $iEkRate = number_format($res['ek']/100, 1, ",", ".");
    $iconnectcost = number_format($res['connectcost']/100, 0, ",", ".");
    $iincludedseconds = number_format($res['includedseconds'], 0, ",", ".");
    echo '<tr>
        <td class="border_tds">' . h($res['comment']) . '</td>
        <td class="border_tds">' . h($res['trunks']) . '</td>
        <td class="border_tds">' . h($res['pattern']) . '</td>
        <td class="border_tds">' . $iEkRate . ' ' . h(translate("centperminute")) . '</td>
        <td class="border_tds">' . $iRate . ' ' . h(translate("centperminute")) . '</td>
        <td class="border_tds">' . $iconnectcost . ' Cents</td>
        <td class="border_tds">' . $iincludedseconds . ' Seconds</td>
        <td class="border_tds"><a href="' . h($_SERVER['PHP_SELF']) . '?action=edit&pat=' . urlencode($res['pattern']) . '">
            <img src="imgs/info.gif" width="12" height="12" alt="Info/Edit" title="Info/Edit" /></a> </td>
        <td class="border_tds"><a href="javascript:if(confirm(\'' . h(translate("adminratesconfirmdelete") . ' ' . $res['pattern']) . '\')) document.location.href
            =\'' . h($_SERVER['PHP_SELF']) . '?action=del&pat=' . urlencode($res['pattern']) . '&let=' . urlencode($letter) . '\';">
            <img src="imgs/del.gif" width="12" height="12" alt="Delete" title="Delete" /></a></td>
        <td class="border_tds">';
    // Is in free package
    $exist = pg_prepare($dbcon, "free_check", "SELECT pattern FROM v_free WHERE CAST(pattern AS TEXT) = $1");
    $existRes = pg_execute($dbcon, "free_check", [$res['pattern']]);
    if (pg_num_rows($existRes) != 0) {
        echo ' Free';
    }
    echo '</td></tr>';
}
echo '<tr><td class="gapright" colspan="9"><a class="big_links" href="' . h($_SERVER['PHP_SELF']) . '?action=add">Add new destination</a></td></tr></table><br/>';

echo '<p align="center"><a href="importsql.php">Import from cvs database click here</a></p>';
require("footer.php");
?>
