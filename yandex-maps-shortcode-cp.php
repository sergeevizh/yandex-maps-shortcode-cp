<?php
/*
	Plugin Name: Yandex.Maps simple shortcode by CasePress
	Plugin URI:  https://docs.google.com/document/d/1xltAdrdcyJ9yzsRp63jA7MMQu1nYsqgkFzcX2SDUZ24/edit?usp=sharing
	Description: Плагин позволяет вывести Яндекс.Карты через шорткод, с основными параметрами. Поддерживает YMapsML. Прост для доработок.
	Version: 1.1
	Author: CasePress Studio
	Author URI: http://casepress.org/
*/


/*
Делаем шорткод вида [yam xml=http://site.com/maps.xml]
*/

add_shortcode( 'ya-map-cp', 'yam_render' );
function yam_render($atts){

//обработчик параметров шорткода
extract( shortcode_atts( array(
        'lng' => '',		// координаты
		'lat' => '',
		'h' => '',
        'ymapsml_url' => plugin_dir_url(__FILE__).'examples/example_yandex_maps_api21.xml', // xml YMapsML
		'map_width_px' => '600',
		'map_height_px' => '400',
		'map_center' => '[55.76, 37.64]',
		'map_scale' => '6',
		'map_type' => 'yandex#map',
		'div_id' => 'map',
    ), $atts ) );
		
	ob_start();  

	?>
		<script type="text/javascript">
			var cpMap;

			// Дождёмся загрузки API и готовности DOM.
			ymaps.ready(init);

			function init () {
				// Создание экземпляра карты и его привязка к контейнеру с
				// заданным id ("map").
				cpMap = new ymaps.Map('<?php echo $div_id ?>', {
					// При инициализации карты обязательно нужно указать
					// её центр и коэффициент масштабирования.
					center: <?php echo $map_center ?>, // Москва
					zoom: <?php echo $map_scale ?>,
					//type: <?php echo $map_type ?>,
				});
				
				//Добавляем удобство, которое при клике по карте скрывает балуны
				cpMap.events.add('click', function () {
					cpMap.balloon.close();
				});
				
				<?php 
				//Если есть xml, то загружаем его	
				if(isset($ymapsml_url)): ?>
					ymaps.geoXml.load('<?php echo $ymapsml_url; ?>').then(onGeoXmlLoad);
				<?php endif;?>

			}
			
			//Функция обработки данных XML в объекты на карте
			function onGeoXmlLoad (res) {
				cpMap.geoObjects.add(res.geoObjects);
				if (res.mapState) {
					res.mapState.applyToMap(cpMap);
				}
			}
		</script>
		<div id="<?php echo $div_id ?>" <?php echo 'style="'.'width:'.$map_width_px.'px;'.'height:'.$map_height_px.'px;'.'"'; ?> class="cp-ya-map"></div>

	
	<?
	$object .= ob_get_contents();  
	ob_end_clean();

return $object;
	
}

// Register Script
function cp_add_ya_map_api() {
global $post;
wp_register_script('yandex-maps-api', '//api-maps.yandex.ru/2.1/?lang=ru_RU', false, '2.1', $in_footer=false );

	//Загружаем скрипт только если есть шорткод у поста	
	if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'ya-map-cp') ) {
		wp_enqueue_script( 'yandex-maps-api' );
	}
}
// Hook into the 'wp_enqueue_scripts' action
add_action( 'wp_enqueue_scripts', 'cp_add_ya_map_api' );