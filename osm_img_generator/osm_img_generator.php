<?hh

require_once 'OsmTileGenerator.php';

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
async function main_tile_generator(): Awaitable<void> {
  $shortopts = "";
  $shortopts .= "x:";  // tile x
  $shortopts .= "y:";  // tile y
  $shortopts .= "l:";  // tile level

  $longopts  = array(
      "tile_x:",
      "tile_y:",
      "tile_l:",
  );
  $options = getopt($shortopts, $longopts);

  $tile_x = idx($options, 'x');
  $tile_y = idx($options, 'y');
  $tile_l = idx($options, 'l');
  if ($tile_x === null || $tile_y === null || $tile_l === null) {
    printf("need tile x, y, l\n");
    return;
  }

  $succeeded = await OsmTileGenerator::create()->gen(
    (int)$tile_x,
    (int)$tile_y,
    (int)$tile_l,
  );
  printf("succeeded:%d\n", $succeeded);
}

\HH\Asio\join(main_tile_generator());
