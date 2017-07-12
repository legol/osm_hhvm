<?hh

require_once 'OSMModel.php';

class PostgresqlAdapter {
  private $conn = null;
  private bool $save_node_sql_prepared = false;
  private bool $save_way_sql_prepared = false;
  private bool $save_relation_sql_prepared = false;
  private bool $save_tag_sql_prepared = false;
  private bool $gen_all_ways_sql_saved = false;
  private bool $gen_points_of_way_sql_saved = false;
  private bool $gen_save_way_bounding_box_sql_saved = false;

  public static function create(): PostgresqlAdapter {
    return new PostgresqlAdapter();
  }

  public function __construct() {
  }

  public function __destruct () {
  }

  public function connect(): bool {
    $host = "host = 192.168.1.111";
    $port = "port = 5432";
    $dbname = "dbname = postgres";
    $credentials = "user=postgres password=CjTm803048";

    $this->conn = pg_connect("$host $port $dbname $credentials");
    return ($this->conn !== null);
  }

  public function close(): void {
    if ($this->conn !== null) {
      pg_close($this->conn);
    }

    $this->conn = null;
  }

  public async function genSaveNode(OSMNode $node): Awaitable<bool> {
    if ($this->save_node_sql_prepared === false) {
      $result = pg_prepare(
        $this->conn,
        "insert_node",
        'INSERT INTO
          node(
            id,
            visible,
            version,
            changeset,
            "timestamp",
            "user",
            uid,
            wgs84long_lat
          ) VALUES (
            $1,
            $2,
            $3,
            $4,
            to_timestamp($5, \'YYYY-MM-DD HH24:MI:SS\'),
            $6,
            $7,
            ST_SetSRID(ST_MakePoint($8, $9), 4326)
          )',
      );
      if ($result === FALSE) {
        return false;
      }

      $this->save_node_sql_prepared = true;
    }

    $result = pg_execute($this->conn, "insert_node", array(
      idx($node['attributes'], 'id'),
      idx($node['attributes'], 'visible'),
      idx($node['attributes'], 'version'),
      idx($node['attributes'], 'changeset'),
        str_replace(
          'Z',
          '',
          str_replace('T', ' ', idx($node['attributes'], 'timestamp')),
        ),
      idx($node['attributes'], 'user'),
      idx($node['attributes'], 'uid'),
      idx($node['attributes'], 'lon'),
      idx($node['attributes'], 'lat'),
    ));
    if ($result === FALSE) {
      return false;
    }

    $result = await $this->genSaveTag(
      $node['tags'],
      OSMTypes::NODE,
      (int)(idx($node['attributes'], 'id')),
    );

    return $result;
  }

  private async function genSaveTag(
    Vector<OSMTag> $tags,
    OSMTypes $tag_type,
    int $ref_id,
  ): Awaitable<bool> {
    if ($this->save_tag_sql_prepared === false) {
      $result = pg_prepare(
        $this->conn,
        "insert_tag_node",
        'INSERT INTO node_tag(nd_ref, k, v) VALUES ($1, $2, $3)',
      );
      if ($result === FALSE) {
        return false;
      }

      $result = pg_prepare(
        $this->conn,
        "insert_tag_way",
        'INSERT INTO way_tag(way_ref, k, v) VALUES ($1, $2, $3)',
      );
      if ($result === FALSE) {
        return false;
      }

      $result = pg_prepare(
        $this->conn,
        "insert_tag_relation",
        'INSERT INTO relation_tag(relation_ref, k, v) VALUES ($1, $2, $3)',
      );
      if ($result === FALSE) {
        return false;
      }

      $this->save_tag_sql_prepared = true;
    }

    $statements = Map {
      OSMTypes::NODE => 'insert_tag_node',
      OSMTypes::WAY => 'insert_tag_way',
      OSMTypes::RELATION => 'insert_tag_relation',
    };
    $statement = idx($statements, $tag_type);
    if ($statement === null) {
      return false;
    }

    foreach ($tags as $tag) {
      $result = pg_execute($this->conn, $statement, array(
        $ref_id,
        $tag['k'],
        $tag['v'],
      ));
      if ($result === FALSE) {
        return false;
      }
    }

    return true;
  }

  public async function genSaveWay(OSMWay $way): Awaitable<bool> {
    if ($this->save_way_sql_prepared === false) {
      $result = pg_prepare(
        $this->conn,
        "insert_way",
        'INSERT INTO
          way(
            id,
            visible,
            version,
            changeset,
            "timestamp",
            "user",
            uid
          ) VALUES (
            $1,
            $2,
            $3,
            $4,
            to_timestamp($5, \'YYYY-MM-DD HH24:MI:SS\'),
            $6,
            $7
          )',
      );
      if ($result === FALSE) {
        return false;
      }

      $result = pg_prepare(
        $this->conn,
        "insert_way_nd",
        'INSERT INTO way_nd(way_ref, nd_ref, "order") VALUES ($1, $2, $3)',
      );
      if ($result === FALSE) {
        return false;
      }

      $this->save_way_sql_prepared = true;
    }

    $result = pg_execute($this->conn, "insert_way", array(
      idx($way['attributes'], 'id'),
      idx($way['attributes'], 'visible'),
      idx($way['attributes'], 'version'),
      idx($way['attributes'], 'changeset'),
        str_replace(
          'Z',
          '',
          str_replace('T', ' ', idx($way['attributes'], 'timestamp')),
        ),
      idx($way['attributes'], 'user'),
      idx($way['attributes'], 'uid'),
    ));
    if ($result === FALSE) {
      return false;
    }

    $result = await $this->genSaveTag(
      $way['tags'],
      OSMTypes::WAY,
      (int)(idx($way['attributes'], 'id')),
    );

    $order = 0;
    foreach ($way['nodes'] as $node_id) {
      $result = pg_execute($this->conn, "insert_way_nd", array(
        idx($way['attributes'], 'id'),
        $node_id,
        $order,
      ));
      if ($result === FALSE) {
        return false;
      }

      $order++;
    }

    return true;
  }

  public async function genSaveRelation(OSMRelation $relation): Awaitable<bool> {
    if ($this->save_relation_sql_prepared === false) {
      $result = pg_prepare(
        $this->conn,
        "insert_relation",
        'INSERT INTO
          relation(
            id,
            visible,
            version,
            changeset,
            "timestamp",
            "user",
            uid
          ) VALUES (
            $1,
            $2,
            $3,
            $4,
            to_timestamp($5, \'YYYY-MM-DD HH24:MI:SS\'),
            $6,
            $7
          )',
      );
      if ($result === FALSE) {
        return false;
      }

      $result = pg_prepare(
        $this->conn,
        "insert_relation_member",
        'INSERT INTO relation_member(relation_ref, type, ref, role, "order") VALUES ($1, $2, $3, $4, $5)',
      );
      if ($result === FALSE) {
        return false;
      }

      $this->save_relation_sql_prepared = true;
    }

    $result = pg_execute($this->conn, "insert_relation", array(
      idx($relation['attributes'], 'id'),
      idx($relation['attributes'], 'visible'),
      idx($relation['attributes'], 'version'),
      idx($relation['attributes'], 'changeset'),
        str_replace(
          'Z',
          '',
          str_replace('T', ' ', idx($relation['attributes'], 'timestamp')),
        ),
      idx($relation['attributes'], 'user'),
      idx($relation['attributes'], 'uid'),
    ));
    if ($result === FALSE) {
      return false;
    }

    $result = await $this->genSaveTag(
      $relation['tags'],
      OSMTypes::RELATION,
      (int)(idx($relation['attributes'], 'id')),
    );

    $order = 0;
    foreach ($relation['members'] as $member) {
      $result = pg_execute($this->conn, "insert_relation_member", array(
        idx($relation['attributes'], 'id'),
        $member['type'],
        $member['ref'],
        $member['role'],
        $order,
      ));
      if ($result === FALSE) {
        return false;
      }

      $order++;
    }

    return true;
  }

  public async function genAllWays(int $offset): Awaitable<?Vector<int>> {
    if ($this->gen_all_ways_sql_saved === false) {
      $result = pg_prepare(
        $this->conn,
        "gen_all_ways",
        'select way.id as way_ref from way offset $1 limit $2',
      );
      if ($result === FALSE) {
        return null;
      }

      $this->gen_all_ways_sql_saved = true;
    }

    $limit = 1000;
    $result = pg_execute($this->conn, "gen_all_ways", array($offset, $limit));
    if ($result === FALSE) {
      return null;
    }

    $ways = Vector {};
    while (($row = pg_fetch_row($result)) !== FALSE) {
      $ways[] = (int)($row[0]);
    }

    return $ways;
  }

  public async function genPointsOfWay(
    int $way_id,
  ): Awaitable<?Vector<OSMPoint>> {
    if ($this->gen_points_of_way_sql_saved === false) {
      $result = pg_prepare(
        $this->conn,
        "gen_points_of_way",
        '
          select id as nd_ref, ST_Y(node.wgs84long_lat) as lat, ST_X(node.wgs84long_lat) as lon from node
          right join
            (select way_nd.nd_ref from way_nd where way_nd.way_ref=$1 order by "order" desc) as nodes
          on node.id = nodes.nd_ref
        ',
      );
      if ($result === FALSE) {
        return null;
      }

      $this->gen_points_of_way_sql_saved = true;
    }

    $result = pg_execute($this->conn, "gen_points_of_way", array($way_id));
    if ($result === FALSE) {
      return null;
    }

    $points_of_way = Vector {};
    while (($row = pg_fetch_row($result)) !== FALSE) {
      $points_of_way[] = shape(
        'node_id' => (int)($row[0]),
        'latitude' => (float)($row[1]),
        'longitude' => (float)($row[2]),
      );
    }

    return $points_of_way;
  }

  public async function genSaveWayBoundingBox(
    int $way_id,
    OSMBoundingBox $bounding_box,
  ): Awaitable<bool> {
    if ($this->gen_save_way_bounding_box_sql_saved === false) {
      $result = pg_prepare(
        $this->conn,
        "gen_save_way_bounding_box",
        '
          insert into way_bounding_box(
            way_ref,
            minlat,
            minlon,
            maxlat,
            maxlon,
            wgs84_bounding_box
          ) values (
            $1,
            $2,
            $3,
            $4,
            $5,
            ST_SetSRID(ST_Envelope(ST_MakeLine(ST_MakePoint($6, $7), ST_MakePoint($8, $9))), 4326)
          )
        ',
      );
      if ($result === FALSE) {
        return false;
      }

      $this->gen_save_way_bounding_box_sql_saved = true;
    }

    $result = pg_execute(
      $this->conn,
      "gen_save_way_bounding_box",
      array(
        $way_id,
        $bounding_box['minlat'],
        $bounding_box['minlon'],
        $bounding_box['maxlat'],
        $bounding_box['maxlon'],

        // top left
        $bounding_box['minlon'],
        $bounding_box['maxlat'],

        // right bottom
        $bounding_box['maxlon'],
        $bounding_box['minlat'],
      ),
    );
    if ($result === FALSE) {
      return false;
    }

    return true;
  }
}
