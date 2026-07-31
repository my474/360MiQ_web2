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

        $data = strtoupper(isset($_GET["data"]) ? formatInput($_GET["data"], $connection) : '');
    	if ($data != "" && (preg_match('/[^A-Za-z0-9&.-]/', $data) || substr_count($data, '.') > 1))
    	    return;

        // Require each selected post to have an inline image, a featured image,
        // or a YouTube embed that the rendering code can display.
        // LEFT JOINs pull the featured image (_thumbnail_id -> _wp_attached_file).
        if ($data == "")
            $sqlQuery = "SELECT a.post_date, a.post_content, LEFT(a.post_title, 120) as post_title, a.post_name, b.meta_value, c.display_name, fm.meta_value as featured_file
                FROM aamiqcom_WP339.deC_posts a
                LEFT JOIN aamiqcom_WP339.deC_usermeta b on a.post_author = b.user_id
                LEFT JOIN aamiqcom_WP339.deC_users c on b.user_id = c.ID
                LEFT JOIN aamiqcom_WP339.deC_postmeta tm on a.ID = tm.post_id and tm.meta_key = '_thumbnail_id'
                LEFT JOIN aamiqcom_WP339.deC_postmeta fm on tm.meta_value = fm.post_id and fm.meta_key = '_wp_attached_file'
                where post_status = 'publish' and post_type = 'post' and (b.meta_key ='nickname' and b.meta_value = display_name)
                and a.post_date > DATE_SUB(NOW(), INTERVAL '30' DAY)
                and post_title NOT REGEXP '[0-9]{4}-[0-9]{2}-[0-9]{2}$'

                AND (
                    a.post_content LIKE '%<img%'
                    OR (fm.meta_value IS NOT NULL AND fm.meta_value <> '')
                    OR a.post_content LIKE '%wp:embed {\"url\":\"https://youtu%'
                )
				
				-- Exclude posts assigned to these categories
				AND NOT EXISTS (
				    SELECT 1
					FROM aamiqcom_WP339.deC_term_relationships AS tr_ex
					JOIN aamiqcom_WP339.deC_term_taxonomy AS tt_ex
					    ON tr_ex.term_taxonomy_id = tt_ex.term_taxonomy_id
					JOIN aamiqcom_WP339.deC_terms AS t_ex
					    ON tt_ex.term_id = t_ex.term_id
					WHERE tr_ex.object_id = a.ID
						AND tt_ex.taxonomy = 'category'
						AND t_ex.name IN (
							'NSE Market Update',
							'ASX Market Update',
							'HK Stock Analysis',
							'HKEX Market Update',
							'SHSE Market Update',
							'TSX Market Update'
						)
				)
                order by post_date desc limit 5";
        else
            $sqlQuery = "SELECT post_date, post_content, post_title, post_name,
					   b.meta_value, c.display_name,
					   fm.meta_value AS featured_file
				FROM aamiqcom_WP339.deC_posts AS a
				LEFT JOIN aamiqcom_WP339.deC_usermeta AS b
					ON a.post_author = b.user_id
				JOIN aamiqcom_WP339.deC_users AS c
					ON b.user_id = c.ID
				JOIN aamiqcom_WP339.deC_term_relationships AS tr
					ON a.ID = tr.object_id
				JOIN aamiqcom_WP339.deC_term_taxonomy AS tt
					ON tr.term_taxonomy_id = tt.term_taxonomy_id
				JOIN aamiqcom_WP339.deC_terms AS t
					ON tt.term_id = t.term_id
				LEFT JOIN aamiqcom_WP339.deC_postmeta AS tm
					ON a.ID = tm.post_id
					AND tm.meta_key = '_thumbnail_id'
				LEFT JOIN aamiqcom_WP339.deC_postmeta AS fm
					ON tm.meta_value = fm.post_id
					AND fm.meta_key = '_wp_attached_file'
				WHERE b.meta_key = 'nickname'
					AND b.meta_value = display_name
					AND tt.taxonomy = 'post_tag'
					AND t.name = '$data'
					AND a.post_type = 'post'
					AND a.post_status = 'publish'
					AND post_date > DATE_SUB(NOW(), INTERVAL 60 DAY)
					AND (
						a.post_content LIKE '%<img%'
						OR (fm.meta_value IS NOT NULL AND fm.meta_value <> '')
						OR a.post_content LIKE '%wp:embed {\"url\":\"https://youtu%'
					)

					-- Exclude posts assigned to these categories
					AND NOT EXISTS (
						SELECT 1
						FROM aamiqcom_WP339.deC_term_relationships AS tr_ex
						JOIN aamiqcom_WP339.deC_term_taxonomy AS tt_ex
							ON tr_ex.term_taxonomy_id = tt_ex.term_taxonomy_id
						JOIN aamiqcom_WP339.deC_terms AS t_ex
							ON tt_ex.term_id = t_ex.term_id
						WHERE tr_ex.object_id = a.ID
							AND tt_ex.taxonomy = 'category'
							AND t_ex.name IN (
								'NSE Market Update',
								'ASX Market Update',
								'HK Stock Analysis',
								'HKEX Market Update',
								'SHSE Market Update',
								'TSX Market Update'
							)
					)
				ORDER BY post_date DESC
				LIMIT 5";
            
    	$result = mysqli_query($connection, $sqlQuery);	
    
    	while($itemRow = mysqli_fetch_array($result, MYSQLI_ASSOC))
    	{
    	    $output_array = explode(";", $itemRow['meta_value']);
    	    $avatar = "";
    	    for ($i = 0; $i < sizeof($output_array); $i++)
    	    {
    	        $output_line_array = [];
    	        preg_match('/(360miq.com\/blog\/wp-content\/uploads\/).*(g\")/', $output_array[$i], $output_line_array);
    	        if (sizeof($output_line_array) == 3)
    	        {
    	            $avatar = substr(str_replace(".png", "-96x96.png", str_replace(".jpg", "-96x96.jpg", $output_line_array[0])), 0, -1);
    	            break;
    	        }
    	    }

            // 1. Try inline image in post_content (direct or Jetpack i0.wp.com CDN)
            preg_match('/<img src="https:\/\/(i0\.wp\.com\/)?(360miq\.com\/blog\/wp-content\/uploads\/[^"?]+\.(png|jpg|jpeg|webp))(\?[^"]*)?" alt="/i', $itemRow['post_content'], $img_parts);
            $main_image = !empty($img_parts[2]) ? $img_parts[2] : "";

            // 2. Fall back to featured image (_wp_attached_file), if no inline image found
            if ($main_image == "" && !empty($itemRow['featured_file']))
            {
                $main_image = "360miq.com/blog/wp-content/uploads/" . ltrim($itemRow['featured_file'], '/');
            }

            // 3. If still no image, try youtube
            if ($main_image == "")
            {
                // youtube
        	    preg_match('/(wp:embed {"url":"https:\/\/(www\.)?youtu).*(\",\"type\":\"video\")/', $itemRow['post_content'], $output_array3);
        	    $main_youtube = sizeof($output_array3) == 4 ? rtrim(substr($output_array3[0], 0, strpos($output_array3[0], "\",\"type\":\"video\"") + 1), "\"") : "";
        	    $main_youtube = str_replace("www.", "", $main_youtube);
        	    $main_youtube = str_replace("wp:embed {\"url\":\"https://youtube.com/watch?v=", "", str_replace("wp:embed {\"url\":\"https://youtu.be/", "", $main_youtube));
            }
            else
                $main_youtube = "";

            // Keep the output limited to posts with a renderable image or video.
            if ($main_image == "" && $main_youtube == "")
                continue;
    	    
    	    $post_name = rtrim($itemRow['post_name'], '/') . '/';
            $line = $itemRow['post_title']."@".$post_name."@".$itemRow['display_name']."@".substr($itemRow['post_date'], 0, 10)."@".$avatar."@".$main_image."@".$main_youtube;

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
