<?hh

class OsmParser {
  public static function create(): OsmParser {
    return new OsmParser();
  }

  public async function parse(string $input_file): Awaitable<bool> {
    $node_count = 0;
    $way_count = 0;
    $relation_count = 0;

    $r = new XMLReader();
    $r->open($input_file);
    while ($r->read() && $r->name !== 'bounds');
    while ($r->name === 'bounds') {
      $node = new SimpleXMLElement($r->readOuterXML());

      // printf("name:\n");
      // var_dump($r->name);
      //
      // printf("attributes:\n");
      // foreach ($node->attributes() as $att => $val) {
      //   var_dump($att);
      //   var_dump($val);
      // }

      $r->next('bounds');
    }


    $r = new XMLReader();
    $r->open($input_file);
    while ($r->read() && $r->name !== 'node');
    while ($r->name === 'node') {
      $node = new SimpleXMLElement($r->readOuterXML());

      // printf("name:\n");
      // var_dump($r->name);
      //
      // printf("attributes:\n");
      // foreach ($node->attributes() as $att => $val) {
      //   var_dump($att);
      //   var_dump($val);
      // }

      $node_count++;
      $r->next('node');
    }

    $r = new XMLReader();
    $r->open($input_file);
    while ($r->read() && $r->name !== 'way');
    while ($r->name === 'way') {
      $node = new SimpleXMLElement($r->readOuterXML());

      // printf("name:\n");
      // var_dump($r->name);
      //
      // printf("attributes:\n");
      // foreach ($node->attributes() as $att => $val) {
      //   var_dump($att);
      //   var_dump($val);
      // }

      $way_count++;
      $r->next('way');
    }

    $r = new XMLReader();
    $r->open($input_file);
    while ($r->read() && $r->name !== 'relation');
    while ($r->name === 'relation') {
      $node = new SimpleXMLElement($r->readOuterXML());

      // printf("name:\n");
      // var_dump($r->name);
      //
      // printf("attributes:\n");
      // foreach ($node->attributes() as $att => $val) {
      //   var_dump($att);
      //   var_dump($val);
      // }

      $relation_count++;
      $r->next('relation');
    }

    printf(
      "node:%d way:%d relation:%d\n",
      $node_count,
      $way_count,
      $relation_count,
    );
    return true;
  }
}
