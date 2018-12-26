<?php
	function get_server_response_time($url){
		if(function_exists("fsockopen")){
			$port = preg_match("/^https/", $url) ? 443 : 80;

			$url = preg_replace("/https?\:\/\//", "", $url);

		    $starttime = microtime(true);

		    $file      = @fsockopen($url, 443, $errno, $errstr, 1);
		    $stoptime  = microtime(true);
		    $status    = 0;

		    //echo $stoptime."\n\n";

		    if (!$file){
		        $status = 1000;  // Site is down
		    }
		    else{
		        fclose($file);
		        $status = ($stoptime - $starttime);
		    }

		    return $status;
		}else if(function_exists("curl_init")){
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 1);

			if(curl_exec($ch)){
				$info = curl_getinfo($ch);
			}

			curl_close($ch);

			if(isset($info["http_code"]) && ($info["http_code"] == 200)){
				return round($info["total_time"], 3);
			}else{
				return 1000;
			}
		}else{
			return 0;
		}
	}


	$wpfc_servers = array("de" => array("time" => get_server_response_time("https://api.wpfastestcache.net"),
										"flag" => "de",
										"location" => "Germany",
										"color" => "red"),
						  "mu" => array("time" => get_server_response_time("https://api.wpfastestcache.ga"),
										"flag" => "de",
										"location" => "Germany",
										"color" => "red"),
						  "nl" => array("time" => get_server_response_time("https://api.wpfastestcache.cf"),
										"flag" => "nl",
										"location" => "Holland",
										"color" => "red"),

						  "bg" => array("time" => get_server_response_time("https://api.wpfcbg.tk"),
										"flag" => "bg",
										"location" => "Bulgaria",
										"color" => "red"),


						  "la" => array("time" => get_server_response_time("https://api.wpfcla.tk"),
										"flag" => "us",
										"location" => "Los Ang",
										"color" => "red"),
						  "sg" => array("time" => get_server_response_time("https://api.wpfcsg.tk"),
										"flag" => "sg",
										"location" => "Singapour",
										"color" => "red"),


					 	  "wash" => array("time" => get_server_response_time("https://api.wpfastestcache.gq"),
										"flag" => "us",
										"location" => "Washington DC",
										"color" => "red"),
					 	  "uk" => array("time" => get_server_response_time("https://api.wpfastestcache.ml"),
										"flag" => "gb",
										"location" => "UK",
										"color" => "red"),




					 	  "tx" => array("time" => get_server_response_time("https://api.wpfastestcache.in"),
										"flag" => "us",
										"location" => "TX",
										"color" => "red"),




					 	  "hk" => array("time" => get_server_response_time("https://api.wpfastestcache.tk"),
										"flag" => "hk",
										"location" => "Hong Kong",
										"color" => "red"),
					);

	asort($wpfc_servers);

	foreach ($wpfc_servers as $key => &$value) {
		if(!isset($first)){
			$first = true;
		}else{
			$first = false;
		}

		if($value["time"] == 1000){
			$value["time"] = "Down";
		}
		
		if($first){
			$value["color"] = "#81C564";
		}
	}

	$wpfc_server_location = current(array_keys($wpfc_servers));
?>


<div style="float: right; margin-top:-37px;padding-right: 20px; cursor: pointer;" id="container-show-hide-image-list">
	<span id="show-image-list">Show Images</span>
	<span style="display:none;" id="hide-image-list">Hide Images</span>
</div>
<div id="wpfc-image-static-panel" style="width:100%;float:left;">
	<div style="float: left; width: 100%;">
		<div style="float:left;padding-left: 22px;padding-right:15px;">
			<div style="display: inline-block;">
				<div style="width: 150px; height: 150px; position: relative; border-top-left-radius: 150px; border-top-right-radius: 150px; border-bottom-right-radius: 150px; border-bottom-left-radius: 150px; background-color: #ffcc00;">
					

					<div style="position: absolute; top: 0px; left: 0px; width: 150px; height: 150px; border-top-left-radius: 150px; border-top-right-radius: 150px; border-bottom-right-radius: 150px; border-bottom-left-radius: 150px; clip: rect(0px 150px 150px 75px);">
						<div id="wpfc-pie-chart-little" style="position: absolute; top: 0px; left: 0px; width: 150px; height: 150px; border-top-left-radius: 150px; border-top-right-radius: 150px; border-bottom-right-radius: 150px; border-bottom-left-radius: 150px; clip: rect(0px 75px 150px 0px); -webkit-transform: rotate(36deg); transform: rotate(0deg); background-color: #FFA500;"></div>
					</div>


					<div id="wpfc-pie-chart-big-container-first" style="display:none;position: absolute; top: 0px; left: 0px; width: 150px; height: 150px; border-top-left-radius: 150px; border-top-right-radius: 150px; border-bottom-right-radius: 150px; border-bottom-left-radius: 150px; clip: rect(0px 150px 150px 25px); -webkit-transform: rotate(0deg); transform: rotate(0deg);">
						<div style="position: absolute; top: 0px; left: 0px; width: 150px; height: 150px; border-top-left-radius: 150px; border-top-right-radius: 150px; border-bottom-right-radius: 150px; border-bottom-left-radius: 150px; clip: rect(0px 75px 150px 0px); -webkit-transform: rotate(180deg); transform: rotate(180deg); background-color: #FFA500;"></div>
					</div>
					<div id="wpfc-pie-chart-big-container-second-right" style="display:none;position: absolute; top: 0px; left: 0px; width: 150px; height: 150px; border-top-left-radius: 150px; border-top-right-radius: 150px; border-bottom-right-radius: 150px; border-bottom-left-radius: 150px; clip: rect(0px 150px 150px 75px); -webkit-transform: rotate(180deg); transform: rotate(180deg);">
						<div id="wpfc-pie-chart-big-container-second-left" style="position: absolute; top: 0px; left: 0px; width: 150px; height: 150px; border-top-left-radius: 150px; border-top-right-radius: 150px; border-bottom-right-radius: 150px; border-bottom-left-radius: 150px; clip: rect(0px 75px 150px 0px); -webkit-transform: rotate(90deg); transform: rotate(90deg); background-color: #FFA500;"></div>
					</div>

				</div>
				<div style="width: 114px;height: 114px;margin-top: -133px;background-color: white;margin-left: 18px;position: absolute;border-radius: 150px;">
					<p style="text-align:center;margin:27px 0 0 0;color: black;">Succeed</p>
					<p class="wpfc-loading-statics" id="wpfc-optimized-statics-percent" style="text-align: center; font-size: 18px; font-weight: bold; font-family: verdana; margin: -2px 0px 0px; color: black;"></p>
					<p style="text-align:center;margin:0;color: black;">%</p>
				</div>
			</div>
		</div>
		<div id="wpfc-statics-right" style="float: left;padding-left:12px;">
			<ul style="list-style: none outside none;float: left;">
				<li>
					<div style="background-color: rgb(29, 107, 157);width:15px;height:15px;float:left;margin-top:4px;border-radius:5px;"></div>
					<div style="float:left;padding-left:6px;">All JPEG/PNG</div>
					<div class="wpfc-loading-statics" id="wpfc-optimized-statics-total_image_number" style="font-size: 14px; font-weight: bold; color: black; float: left; width: 65%; margin-left: 5px;"></div>
				</li>
				<li>
					<div style="background-color: rgb(29, 107, 157);width:15px;height:15px;float:left;margin-top:4px;border-radius:5px;"></div>
					<div style="float:left;padding-left:6px;">Pending</div>
					<div class="wpfc-loading-statics" id="wpfc-optimized-statics-pending" style="font-size: 14px; font-weight: bold; color: black; float: left; width: 65%; margin-left: 5px;"></div>
				</li>
				<li>
					<div style="background-color: #FF0000;width:15px;height:15px;float:left;margin-top:4px;border-radius:5px;"></div>
					<div style="float:left;padding-left:6px;">Errors</div>
					<div class="wpfc-loading-statics" id="wpfc-optimized-statics-error" style="font-size: 14px; font-weight: bold; color: black; float: left; width: 65%; margin-left: 5px;"></div>
				</li>





				<li style="display:none;">
					<div style="float:left;padding-left:6px;">Server Location</div>
				</li>





			</ul>
			<ul style="list-style: none outside none;float: left;">
				<li>
					<div style="background-color: rgb(61, 207, 60);width:15px;height:15px;float:left;margin-top:4px;border-radius:5px;"></div>
					<div style="float:left;padding-left:6px;"><span>Optimized Images</span></div>
					<div class="wpfc-loading-statics" id="wpfc-optimized-statics-optimized" style="font-size: 14px; font-weight: bold; color: black; float: left; width: 65%; margin-left: 5px;"></div>
				</li>

				<li>
					<div style="background-color: rgb(61, 207, 60);width:15px;height:15px;float:left;margin-top:4px;border-radius:5px;"></div>
					<div style="float:left;padding-left:6px;"><span>Total Reduction</span></div>
					<div class="wpfc-loading-statics" id="wpfc-optimized-statics-reduction" style="font-size: 14px; font-weight: bold; color: black; float: left; width: 80%; margin-left: 5px;"></div>
				</li>
				<li>
					<div id="wpfc-opt-image-loading" style="height: 10px; border: 1px solid rgb(61, 207, 60); width: 130px;padding: 2px;"><div style="height:100%;background-color: rgb(61, 207, 60);width:0;"></div></div>
				</li>



				<li style="display:none;">
					<?php
						foreach ($wpfc_servers as $server_key => $server_value) {
							?>
							<div style="width: 70px;float:left;">
								<input <?php if($wpfc_server_location == $server_key){echo "checked"; }?> value="<?php echo $server_key; ?>" name="wpfc-server-location" type="radio" style="vertical-align: top; padding-top: 0px; margin-top: 0px;"><img src="<?php echo plugins_url("wp-fastest-cache-premium/pro/images/".$server_value["flag"].".png"); ?>">
								<div style="color:black;float: right; width: 62px; text-align: center;font-weight:bold;"><?php echo $server_value["location"]; ?></div>
								<div style="color:<?php echo $server_value["color"]; ?>;float: right; width: 62px; text-align: center;font-weight:bold;"><?php echo $server_value["time"]; ?></div>
							</div>
							<?php
						}
					?>
				</li>



				
			</ul>

			<ul style="list-style: none outside none;float: left;">
				<li>
					<h1 style="margin-top:0;float:left;">Credit: <span class="wpfc-loading-statics" id="wpfc-optimized-statics-credit" style="display: inline-block; height: 16px; width: auto;min-width:25px;"></span></h1>
					<span id="buy-image-credit">More</span>
				</li>
				<li>
					<input style="width:100%;height:110px;" id="wpfc-optimize-images-button" type="submit" value="Optimize All" class="button-primary" />
				</li>
			</ul>
		</div>
	</div>
</div>