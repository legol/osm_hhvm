<?hh

require_once 'PostgresqlAdapter.php';

class OsmParser {
  public static function create(): OsmParser {
    return new OsmParser();
  }

  public async function parse(string $input_file): Awaitable<bool> {
    $r = new XMLReader();
    $r->open($input_file);
    while ($r->read() && $r->name !== 'bounds');
    while ($r->name === 'bounds') {
      $node = new SimpleXMLElement($r->readOuterXML());

      $r->next('bounds');
    }

    $r = new XMLReader();
    $r->open($input_file);
    while ($r->read() && $r->name !== 'node');
    $success = await $this->parse_node($r);
    if ($success === false) {
      return false;
    }

    $r = new XMLReader();
    $r->open($input_file);
    while ($r->read() && $r->name !== 'way');
    $success = await $this->parse_way($r);
    if ($success === false) {
      return false;
    }

    $r = new XMLReader();
    $r->open($input_file);
    while ($r->read() && $r->name !== 'relation');
    $success = await $this->parse_relation($r);
    if ($success === false) {
      return false;
    }

    return true;
  }

  private async function parse_node(XMLReader $r): Awaitable<bool> {
    $node_count = 0;

    $db = PostgresqlAdapter::create();
    if ($db->connect() === false) {
      return false;
    }

    while ($r->name === 'node') {
      $node = new SimpleXMLElement($r->readOuterXML());

      $attributes = Map {};
      foreach ($node->attributes() as $attr_name => $attr_val) {
        $attributes[$attr_name] = (string)$attr_val;
      }

      $tags = Vector {};
      foreach($node->children() as $tag) {
        if ($tag->getName() === 'tag') {
          $osm_tag = shape(
            'k' => (string)($tag->attributes()['k']),
            'v' => (string)($tag->attributes()['v']),
          );

          $tags[] = $osm_tag;
        }
      }

      $osm_node = shape('attributes' => $attributes, 'tags' => $tags);

      $saved = await $db->genSaveNode($osm_node);
      if ($saved === false) {
        return false;
      }

      $node_count++;
      $r->next('node');
    }

    $db->close();
    return true;
  }

  private async function parse_way(XMLReader $r): Awaitable<bool> {
    $way_count = 0;

    $db = PostgresqlAdapter::create();
    if ($db->connect() === false) {
      return false;
    }

    while ($r->name === 'way') {
      $node = new SimpleXMLElement($r->readOuterXML());

      $attributes = Map {};
      foreach ($node->attributes() as $attr_name => $attr_val) {
        $attributes[$attr_name] = (string)$attr_val;
      }

      $tags = Vector {};
      $nodes = Vector {};
      foreach($node->children() as $child) {
        if ($child->getName() === 'tag') {
          $osm_tag = shape(
            'k' => (string)($child->attributes()['k']),
            'v' => (string)($child->attributes()['v']),
          );

          $tags[] = $osm_tag;
        } else if ($child->getName() === 'nd') {
          $nodes[] = (int)($child->attributes()['ref']);
        }
      }

      $osm_way = shape(
        'attributes' => $attributes,
        'tags' => $tags,
        'nodes' => $nodes,
      );

      $saved = await $db->genSaveWay($osm_way);
      if ($saved === false) {
        return false;
      }

      $way_count++;
      $r->next('way');
    }

    $db->close();
    return true;
  }

  private async function parse_relation(XMLReader $r): Awaitable<bool> {
    $relation_count = 0;

    $db = PostgresqlAdapter::create();
    if ($db->connect() === false) {
      return false;
    }

    while ($r->name === 'relation') {
      $node = new SimpleXMLElement($r->readOuterXML());

      $attributes = Map {};
      foreach ($node->attributes() as $attr_name => $attr_val) {
        $attributes[$attr_name] = (string)$attr_val;
      }

      $tags = Vector {};
      $members = Vector {};
      foreach($node->children() as $child) {
        if ($child->getName() === 'tag') {
          $osm_tag = shape(
            'k' => (string)($child->attributes()['k']),
            'v' => (string)($child->attributes()['v']),
          );

          $tags[] = $osm_tag;
        } else if ($child->getName() === 'member') {
          $members[] = shape(
            'ref' => (int) ($child->attributes()['ref']),
            'type' => (string) ($child->attributes()['type']),
            'role' => (string) ($child->attributes()['role']),
          );
        }
      }

      $osm_relation = shape(
        'attributes' => $attributes,
        'tags' => $tags,
        'members' => $members,
      );

      $saved = await $db->genSaveRelation($osm_relation);
      if ($saved === false) {
        return false;
      }

      $relation_count++;
      $r->next('relation');
    }

    $db->close();
    return true;
  }

}
