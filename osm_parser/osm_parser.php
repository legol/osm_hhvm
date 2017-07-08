<?hh

require_once 'OsmParser.php';

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

  await OsmParser::create()->parse($input_file);
}

\HH\Asio\join(main());
