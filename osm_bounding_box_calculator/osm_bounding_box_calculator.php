<?hh

// to run this:
// php /home/ubuntu/git_root/osm_hhvm/osm_parser/osm_parser.php -i/home/ubuntu/git_root/osm_hhvm/test_data/beijing-circle-6.osm

set_include_path(get_include_path().PATH_SEPARATOR.'../osm_parser');
set_include_path(get_include_path().PATH_SEPARATOR.'../osm_img_generator');
set_include_path(
  get_include_path().PATH_SEPARATOR.'../osm_bounding_box_calculator',
);

spl_autoload_extensions('.php');

spl_autoload_register(
  function($class_name) {
    include $class_name.'.php';
  },
);

async function main_bounding_box_calculator(): Awaitable<void> {
  await OsmBoundingBoxCalculator::create()->genCalculateWayBoundingBox();
}

\HH\Asio\join(main_bounding_box_calculator());
