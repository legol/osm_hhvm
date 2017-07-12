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

// $shortopts  = "";
// $shortopts .= "i:";  // Required value
// $shortopts .= "v::"; // Optional value
// $shortopts .= "abc"; // These options do not accept values
//
// $longopts  = array(
//     "required:",     // Required value
//     "optional::",    // Optional value
//     "option",        // No value
//     "opt",           // No value
// );
// $options = getopt($shortopts, $longopts);
// var_dump($options);
async function main(): Awaitable<void> {
  $shortopts = "";
  $shortopts .= "i:";  // input file

  $longopts  = array(
      "input:",     // Required value
  );
  $options = getopt($shortopts, $longopts);

  $input_file = idx($options, 'i');
  if ($input_file === null) {
    printf('need input file\n');
    return;
  }

  $all_succeeded = await OsmParser::create()->parse($input_file);
  printf("all succeeded:%d\n", $all_succeeded);
}

\HH\Asio\join(main());
