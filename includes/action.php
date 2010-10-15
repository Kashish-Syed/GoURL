<?php  //this is the actions used to start the URL building.

require_once 'includes/lilurl.php'; // <- lilURL class file

require_once 'includes/phpqrcode/qrlib.php'; // <- for creating QR codes.

$lilurl = new lilURL();
$lilurl->setAllowedProtocols($allowed_protocols);
$lilurl->setAllowedDomains($allowed_domains);

$msg = '';

$qrCode = '';

$error = false;

if (isset($_GET["url"]) == 'referer' && isset($_SERVER["HTTP_REFERER"])) {
	$_POST['theURL'] = urldecode($_SERVER["HTTP_REFERER"]);
}

if (isset($_POST['theURL'])) {
    $user = $alias = null;
    if ($cas_client->isLoggedIn()) {
        $user = $cas_client->getUser();
        if (!empty($_POST['theAlias'])) { //if the user is CAS authenticated, then he/she can use the $alias
            $alias = $_POST['theAlias'];
        }
    }
    try {
        $url = $lilurl->handlePOST($alias, $user);
        $msg = '<h4>You have a Go URL!</h4><input type="text" onclick="this.select(); return false;" value="'.$url.'" />';
        // $theId = strrchr($url, '/');
        // $qrCode = QRcode::png($url, $imgPath . str_replace('/', '', $theId) . '.png', 'Q', 4, 2);
        
    } catch (Exception $e) {
    	$error = true;
        switch ($e->getCode()) {
            case lilurl::ERR_INVALID_PROTOCOL:
                $msg = '<h4>Whoops, Something Broke</h4><p>Your URL must begin with <code>http://</code>, <code>https://</code> or <code>mailto:</code>.</p>';
                break;
            case lilurl::ERR_INVALID_DOMAIN:
                $msg = '<h4>Whoops, Something Broke</h4><p>You must sign in to create a URL for this domain: '.parse_url($_POST['theURL'], PHP_URL_HOST).'</p>';
                break;
            default:
                $msg = '<h4>Whoops, Something Broke</h4><p>There was an error submitting your url. Check your steps.</p>';
        }
    }
} else {
    // if the form hasn't been submitted, look for an id to redirect to
    $explodo = explode('/', $_SERVER['REQUEST_URI']);
    $id = $explodo[count($explodo)-1];
    echo $id;
    if (!empty($id) && $id != '?login' && $id != '?url=referer') {
        if (!$lilurl->handleRedirect($id)) {
            $msg = '<p class="error">'.htmlentities($id).' - Sorry, but that Go URL isn\'t in our database.</p>';
        }
    }
}
?>
