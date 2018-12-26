<?php
	class WpFastestCacheLazyLoad{
		private $exclude = array();
		private $host = "";
		private $placeholder = "";

		public function __construct(){
			$url = parse_url(site_url());
			$this->host = $url["host"];

			if(isset($GLOBALS["wp_fastest_cache_options"]->wpFastestCacheLazyLoad_keywords) && $GLOBALS["wp_fastest_cache_options"]->wpFastestCacheLazyLoad_keywords){
				$this->exclude = explode(",", $GLOBALS["wp_fastest_cache_options"]->wpFastestCacheLazyLoad_keywords);
			}

			$this->set_placeholder();
		}

		public function set_placeholder(){
			if(isset($GLOBALS["wp_fastest_cache_options"]->wpFastestCacheLazyLoad_placeholder) && $GLOBALS["wp_fastest_cache_options"]->wpFastestCacheLazyLoad_placeholder){
				switch ($GLOBALS["wp_fastest_cache_options"]->wpFastestCacheLazyLoad_placeholder) {
					case "default":
						if(isset($GLOBALS["wp_fastest_cache"]->content_url) && $GLOBALS["wp_fastest_cache"]->content_url){
							$this->placeholder = $GLOBALS["wp_fastest_cache"]->content_url."/plugins/wp-fastest-cache-premium/pro/images/blank.gif";
						}else if(defined('WPFC_WP_CONTENT_URL')){
							$this->placeholder = preg_replace("/https?\:\/\//", "//", WPFC_WP_CONTENT_URL)."/plugins/wp-fastest-cache-premium/pro/images/blank.gif";
						}else{
							$this->placeholder = content_url()."/plugins/wp-fastest-cache-premium/pro/images/blank.gif";
						}
						break;
					case "wpfc":
						$this->placeholder = "//wpfc.ml/b.gif";
						break;
					case "base64":
						$this->placeholder = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";
						break;
				}
			}else{
				$this->placeholder = preg_replace("/https?\:\/\//", "//", WPFC_WP_CONTENT_URL)."/plugins/wp-fastest-cache-premium/pro/images/blank.gif";
			}
		}

		public function is_lazy($img){
			//Slider Revolution
			//<img src="dummy.png" data-lazyload="transparent.png" data-bgposition="center center" data-bgfit="cover" data-bgrepeat="no-repeat" data-bgparallax="off" class="rev-slidebg" data-no-retina>
			if(preg_match("/\sdata-lazyload\=[\"\']/i", $img)){
				return true;
			}

			return false;
		}

		public function mark_attachment_page_images($attr, $attachment) {
			if(isset($attr['src'])){
				if($this->is_thumbnail($attr['src'])){
					return $attr;
				}

				if($this->is_third_party($attr['src'])){
					return $attr;
				}

				if(!$this->is_full('<img src="'.$attr["src"].'" class="'.$attr["class"].'">')){
					return $attr;
				}
			}

			if(!$attachment){
				return $attr;
			}

			$attr['wpfc-lazyload-disable'] = "true";
			
			return $attr;
		}

		public function is_thumbnail($src){
			// < 299x299
			if(preg_match("/\-[12]\d{0,2}x[12]\d{0,2}\.(jpg|jpeg|png)/i", $src)){
				return true;
			}

			// < 299x99
			if(preg_match("/\-[12]\d{0,2}x\d{0,2}\.(jpg|jpeg|png)/i", $src)){
				return true;
			}

			// < 99x299
			if(preg_match("/\-\d{0,2}x[12]\d{0,2}\.(jpg|jpeg|png)/i", $src)){
				return true;
			}

			// < 99x99
			if(preg_match("/\-\d{0,2}x\d{0,2}\.(jpg|jpeg|png)/i", $src)){
				return true;
			}

			return false;
		}

		public function is_third_party($src){
			if(preg_match("/".preg_quote($this->host, "/")."/i", $src)){
				return false;
			}

			return true;
		}

		public function is_full($img){
			// to check homepage. sometimes is_home() does not work
			if(isset($_SERVER["REQUEST_URI"]) && strlen($_SERVER["REQUEST_URI"]) < 2){
				return false;
			}
			
			if(is_home() || is_archive()){
				return false;
			}

			if(preg_match("/-\d+x\d+\.(jpg|jpeg|png)/i", $img)){
				if(preg_match("/\sclass\=[\"\'][^\"\']*size-medium[^\"\']*[\"\']/", $img)){
					return false;
				}
			}

			return true;
		}

		public function mark_content_images($content){
			preg_match_all( '/<img[^\>]+>/i', $content, $matches);

			if(count($matches[0]) > 0){
				foreach ( $matches[0] as $img ) {
					if($this->is_thumbnail($img)){
						continue;
					}

					if($this->is_third_party($img)){
						continue;
					}

					if(!$this->is_full($img)){
						continue;
					}

					$tmp_img = preg_replace("/<img\s/", "<img wpfc-lazyload-disable=\"true\" ", $img);

					$content = str_replace($img, $tmp_img, $content );
				}
			}

			return $content;
		}

		public function is_exclude($source){
			foreach ((array)$this->exclude as $key => $value) {
				if(preg_match("/".preg_quote($value, "/")."/i", $source)){
					return true;
				}
			}

			return false;
		}

		public function images_to_lazyload($content, $inline_scripts) {
			if(isset($GLOBALS["wp_fastest_cache"]->noscript)){
				$inline_scripts = $inline_scripts.$GLOBALS["wp_fastest_cache"]->noscript;
			}

			preg_match_all( '/<img[^\>]+>/i', $content, $matches);

			if(count($matches[0]) > 0){
				foreach ( $matches[0] as $key => $img) {

					if($this->is_exclude($img)){
						continue;
					}

					// don't to the replacement if the image appear in js
					if(!preg_match("/".preg_quote($img, "/")."/i", $inline_scripts)){

						// don't to the replacement if quote of src does not exist
						if(preg_match("/\ssrc\=[\"\']/i", $img)){
							
							// don't to the replacement if the image is a data-uri
							if(!preg_match("/src\=[\'\"]data\:image/i", $img)){
								if(!preg_match("/onload=[\"\']/i", $img)){
									if(preg_match("/wpfc-lazyload-disable/", $img)){
										$tmp_img = preg_replace("/\swpfc-lazyload-disable\=[\"\']true[\"\']\s*/", " ", $img);
									}else{
										if($key < 3){
											$tmp_img = $img;
										}else{
											if(preg_match("/\ssrc\=[\"\'][^\"\']+[\"\']/i", $img)){
												if(preg_match("/mc\.yandex\.ru\/watch/i", $img)){
													$tmp_img = $img;
												}else if($this->is_lazy($img)){
													$tmp_img = $img;
												}else{
													$tmp_img = $img;
													$tmp_img = preg_replace("/\ssrc\=/i", " data-wpfc-original-src=", $tmp_img);
													$tmp_img = preg_replace("/\ssrcset\=/i", " data-wpfc-original-srcset=", $tmp_img);
													$tmp_img = preg_replace("/<img\s/i", "<img onload=\"Wpfcll.r(this,true);\" src=\"".$this->placeholder."$2\" ", $tmp_img);
												}
											}
										}
									}

									$content = str_replace($img, $tmp_img, $content);
								}
							}


						}




						
					}
				}
			}

			return $content;
		}

		public function iframe_to_lazyload($content, $inline_scripts) {
			preg_match_all('/<iframe[^\>]+>/i', $content, $matches);

			if(count($matches[0]) > 0){
				foreach ( $matches[0] as $iframe ) {
					if($this->is_exclude($iframe)){
						continue;
					}
					
					// don't to the replacement if the frame appear in js
					if(!preg_match("/".preg_quote($iframe, "/")."/i", $inline_scripts)){
						if(!preg_match("/onload=[\"\']/i", $iframe)){

							if(preg_match("/youtube\.com\/embed/i", $iframe) && !preg_match("/videoseries\?list/i", $iframe)){
								// to exclude /videoseries?list= because of problem with getting thumbnail
								$tmp_iframe = preg_replace("/\ssrc\=[\"\'](https?\:)?\/\/(www\.)?youtube\.com\/embed\/([^\"\']+)[\"\']/i", " onload=\"Wpfcll.r(this,true);\" data-wpfc-original-src=\"".WPFC_WP_CONTENT_URL."/plugins/wp-fastest-cache-premium/pro/templates/youtube.html#$3\"", $iframe);
							}else{
								$tmp_iframe = preg_replace("/\ssrc\=/i", " onload=\"Wpfcll.r(this,true);\" data-wpfc-original-src=", $iframe);
							}

							$content = str_replace($iframe, $tmp_iframe, $content);
						}
					}
				}
			}

			return $content;
		}

		public static function get_js_source_new(){
			$js = "\n<script data-wpfc-render=\"false\">".file_get_contents(WPFC_WP_PLUGIN_DIR."/wp-fastest-cache-premium/pro/js/lazy-load-new.js")."</script>\n";
			
			$js = preg_replace("/\/\*[^\n]+\*\//", "", $js); //to remove comments
			$js = preg_replace("/var\sself/", "var s", $js);
			$js = preg_replace("/self\./", "s.", $js);
			$js = preg_replace("/Wpfc_Lazyload/", "Wpfcll", $js);
			$js = preg_replace("/(\.?)init(\:?)/", "$1i$2", $js);
			$js = preg_replace("/(\.?)load_images(\:?)/", "$1li$2", $js);
			$js = preg_replace("/\s*(\+|\=|\:|\;|\{|\}|\,)\s*/", "$1", $js);
			$js = preg_replace("/originalsrcset/", "ot", $js);
			$js = preg_replace("/originalsrc/", "oc", $js);
			$js = preg_replace("/load_sources/", "ls", $js);
			$js = preg_replace("/set_source/", "ss", $js);
			$js = preg_replace("/sources/", "s", $js);
			$js = preg_replace("/winH/", "w", $js);
			$js = preg_replace("/number/", "n", $js);
			$js = preg_replace("/elemRect/", "er", $js);
			$js = preg_replace("/parentRect/", "pr", $js);
			$js = preg_replace("/parent([^N])/", "p$1", $js);
			$js = preg_replace("/top(\=|\+)/", "t$1", $js);


			//$content = substr_replace($content, $js."\n"."</body>", strripos($content, "</body>"), strlen("</body>"));

			return $js;
		}

		public static function get_js_source(){
			$js = "\n<script data-wpfc-render=\"false\">".file_get_contents(WPFC_WP_PLUGIN_DIR."/wp-fastest-cache-premium/pro/js/lazy-load.js")."</script>\n";
			
			$js = preg_replace("/var\sself/", "var s", $js);
			$js = preg_replace("/self\./", "s.", $js);
			$js = preg_replace("/Wpfc_Lazyload/", "Wpfcll", $js);
			$js = preg_replace("/(\.?)init(\:?)/", "$1i$2", $js);
			$js = preg_replace("/(\.?)load_images(\:?)/", "$1li$2", $js);
			$js = preg_replace("/\s*(\=|\:|\;|\{|\}|\,)\s*/", "$1", $js);
			$js = preg_replace("/originalsrcset/", "osrcs", $js);
			$js = preg_replace("/originalsrc/", "osrc", $js);


			//$content = substr_replace($content, $js."\n"."</body>", strripos($content, "</body>"), strlen("</body>"));

			return $js;
		}
	}
?>