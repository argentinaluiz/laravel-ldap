<?php
define('DOMAIN_FQDN', 'son.com'); //Replace with REAL DOMAIN FQDN
define('LDAP_SERVER', '192.168.0.200');  //Replace with REAL LDAP SERVER Address

if (isset($_POST['submit'])) {
    $user = strip_tags($_POST['username']) . '@' . DOMAIN_FQDN;
    $pass = stripslashes($_POST['password']);
    $conn = ldap_connect("ldap://" . LDAP_SERVER . "/");
    if (!$conn) {
        $err = 'Could not connect to LDAP server';
    } else {
        $bind = @ldap_bind($conn, $user, $pass);
        ldap_get_option($conn, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error);
        if (!empty($extended_error)) {
            $errno = explode(',', $extended_error);
            $errno = $errno[2];
            $errno = explode(' ', $errno);
            $errno = $errno[2];
            $errno = intval($errno);
            if ($errno == 532) {
                $err = 'Unable to login: Password expired';
            }
        } elseif ($bind) {
            $base_dn = array(
                "CN=Users,DC=" . join(',DC=', explode('.', DOMAIN_FQDN)),
                "OU=Users,OU=People,DC=" . join(',DC=', explode('.', DOMAIN_FQDN))
            );
            $result = ldap_search(array($conn, $conn), $base_dn, "(cn=*)");
            if (!count($result)) {
                $err = 'Result: ' . ldap_error($conn);
            } else {
                echo "Success";
            }
        }
    }
    (!isset($err)){
    $err = 'Result: ' . ldap_error($conn)
    };

    ldap_close($conn);

}
?>
<!DOCTYPE html>
<head>
    <title>PHP LDAP LOGIN</title>
</head>
<body>
<form method="post">
    <p>
        <?php
        if (isset($err)) {
            echo $err;
        }
        ?>
    </p>

    <label>Login:</label>
    <input type="text" name="username" autocomplete="off"/>
    <label>Password:</label>
    <input type="password" name="password" autocomplete="off"/>
    <input class="button" type="submit" name="submit" value="Login"/>
</form>
</body>
</html>
