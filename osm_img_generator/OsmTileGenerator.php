<?hh

// require_once 'PostgresqlAdapter.php';

class OsmTileGenerator {
  public static function create(): OsmTileGenerator {
    return new OsmTileGenerator();
  }

  public async function gen(
    string $lat_lon_level,
  ): Awaitable<?Imagick> {
    $image_width = 256;
    $image_height = 256;

    $matched = array();
    preg_match_all("/(.*),(.*),(.*)/", $lat_lon_level, $matched);

    if ((new Vector($matched))->count() !== 4) {
      return null;
    }

    $latitude = (float)$matched[1][0];
    $longitude = (float)$matched[2][0];
    $tile_level = (int)$matched[3][0];

    $bounding_box = OsmTileUtils::latLongToTileBoundingBox(
      $latitude,
      $longitude,
      $tile_level,
    );

    $img = new Imagick();
    $img->newImage($image_width, $image_height, "none");
    $img->setImageFormat ("png");

    $layers = await $this->genLayers($bounding_box, $tile_level);
    if ($layers === null) {
      return $img;
    }

    // $red = new ImagickDraw();
    // $red->setFillColor("#FF0000");
    // $red->rectangle(0.0, 0.0, 50.0, 200.0);
    //
    // $img->drawImage($red);
    //
    return await $this->genRender($layers, $tile_level, $img);
  }

  private async function genLayers(
    OSMBoundingBox $bounding_box,
    int $tile_level,
  ): Awaitable<?Map<string, Vector<OSMLayer>>> {
    $db = PostgresqlAdapter::create();
    if ($db->connect() === false) {
      return null;
    }

    $layers = Map {};

    $layers["layer_land"] = Vector {};
    $layers["layer_natural"] = Vector {};
    $layers["layer_building"] = Vector {};
    $layers["layer_water"] = Vector {};
    $layers["layer_highway"] = Vector {};
    $layers["layer_highway_link"] = Vector {};
    $layers["layer_other"] = Vector {};
    $layers["layer_tag"] = Vector {};
    $layers["layer_boundary"] = Vector {};
    $layers["layer_debug"] = Vector {};

    $ways = await $db->genWaysByBoundingBox($bounding_box);
    if ($ways === null) {
      return null;
    }

    await \HH\Asio\m(array_map(
      async $way_id ==> await $this->genCategorizeWays($way_id, $layers),
      $ways,
    ));

    $db->close();
    return $layers;
  }

  private async function genCategorizeWays(
    int $way_id,
    Map<string, Vector<OSMLayer>> &$layers,
  ): Awaitable<void> {

  }

  private async function genRender(
    Map<string, Vector<OSMLayer>> $layers,
    int $tile_level,
    Imagick $img,
  ): Awaitable<Imagick> {
    return $img;
  }
}
