<?php
    if (!isset($_SERVER["HTTP_X_REQUESTED_WITH"]) ||
        (empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest")) {
        if (realpath($_SERVER["SCRIPT_FILENAME"]) == __FILE__) { // direct access denied
            header("Location: /");
            exit;
        }
    }

    $browser = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    if (strpos($referer, 'https://360miq.com') === 0 ||
        strpos($referer, 'https://www.360miq.com') === 0 ||
        strpos($referer, 'https://aamiq.com') === 0 ||
        strpos($referer, 'https://www.aamiq.com') === 0 ||
        ((strpos($browser, 'PaleMoon') !== false || strpos($browser, 'Firefox') !== false) && $referer == '')) // Palemoon, WaterFox, SeaMonkey has no referer
    {
        define('A',true);
        include '/home2/aamiqcom/php_script/mysql_vars_blog.php';

        $sqlQuery = "SELECT post_title, post_name
                     FROM `deC_posts`
                     WHERE post_status = 'publish'
                       AND post_type = 'post'
                     ORDER BY CASE
                                  WHEN post_author NOT IN (0, 1, 2, 116)
                                   AND post_date >= DATE_SUB(NOW(), INTERVAL 5 DAY)
                                  THEN 0
                                  ELSE 1
                              END,
                              post_date DESC
                     LIMIT 8";

        $result = mysqli_query($connection, $sqlQuery);

        while($itemRow = mysqli_fetch_array($result, MYSQLI_ASSOC))
        {
            $post_name = rtrim($itemRow['post_name'], '/') . '/';
            $line = $itemRow['post_title']."@".$post_name;

            if ($line != "" )
            {
                echo $line . "\n";
            }
        }

        mysqli_close($connection);
    }
    else
        header("Location: /");
?>
