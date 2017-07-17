<?hh

class OsmTileGenerator {
  public static function create(): OsmTileGenerator {
    return new OsmTileGenerator();
  }

  public async function gen(
    string $lat_lon_level,
  ): Awaitable<?Imagick> {
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

    $all_layers = await $this->genLayers($bounding_box, $tile_level);
    if ($all_layers === null) {
      return null;
    }

    return await $this->genRender($all_layers, $tile_level, $bounding_box);
  }

  private async function genLayers(
    OSMBoundingBox $bounding_box,
    int $tile_level,
  ): Awaitable<?Map<int /* layer index*/, Map<string, Vector<OSMLayer>>>> {
    $db = PostgresqlAdapter::create();
    if ($db->connect() === false) {
      return null;
    }

    $ways = await $db->genWaysByBoundingBox($bounding_box);
    if ($ways === null) {
      return null;
    }

    // layer index is between -5..5. see http://wiki.openstreetmap.org/wiki/Map_Features#Highway
    $all_layers = Map {};
    for ($layer_index = -5; $layer_index <= 5; $layer_index++) {
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
      $layers["layer_rail"] = Vector {};
      $layers["layer_debug"] = Vector {};

      $all_layers[$layer_index] = $layers;
    }

    // $idx = 0;
    // foreach ($ways as $way_id) {
    //   printf("processing way:%d, idx:%d\n", $way_id, $idx++);
    //   await $this->genCategorizeWays(93689238, $all_layers);
    // }

    await \HH\Asio\v(array_map(
      async $way_id ==> await $this->genCategorizeWays($way_id, $all_layers),
      $ways,
    ));

    $db->close();
    return $all_layers;
  }

  private async function genCategorizeWays(
    int $way_id,
    Map<int, Map<string, Vector<OSMLayer>>> &$all_layers,
  ): Awaitable<bool> {
    log_message('info', "processing way:".$way_id);

    $db = PostgresqlAdapter::create();
    if ($db->connect() === false) {
      return false;
    }

    list($points, $tags) = await \HH\Asio\va(
      $db->genPointsOfWay($way_id),
      $db->genTags($way_id, 'way'),
    );

    if ($points === null || $tags === null) {
      return false;
    }

    $layer_index = (int)(idx($tags, 'layer', 0));
    $layers = idx($all_layers, $layer_index);
    if ($layers === null) {
      return false;
    }

    if (OsmUtils::isBuilding($tags)) {
      $layers["layer_building"][] = shape(
        'points' => $points,
        'tags' => $tags,
      );
    } else if (OsmUtils::isLand($tags) ||
               OsmUtils::isLeisure($tags) ||
               OsmUtils::isAmenity($tags)) {
      $layers["layer_land"][] = shape('points' => $points, 'tags' => $tags);
      $layers["layer_tag"][] = shape('points' => $points, 'tags' => $tags);
    } else if (OsmUtils::isWaterway($tags)) {
      $layers["layer_water"][] = shape('points' => $points, 'tags' => $tags);
      $layers["layer_tag"][] = shape('points' => $points, 'tags' => $tags);
    } else if (OsmUtils::isNetural($tags)) {
      $layers["layer_natural"][] = shape(
        'points' => $points,
        'tags' => $tags,
      );
    } else if (OsmUtils::isHighway($tags)) {
      $highwayValue = $tags['highway'];

      if ($highwayValue === 'motorway_link' ||
          $highwayValue === 'trunk_link' ||
          $highwayValue === 'primary_link' ||
          $highwayValue === 'secondary_link' ||
          $highwayValue === 'tertiary_link') {
        $layers["layer_highway_link"][] = shape(
          'points' => $points,
          'tags' => $tags,
        );
      } else {
        $layers["layer_highway"][] = shape(
          'points' => $points,
          'tags' => $tags,
        );
        $layers["layer_tag"][] = shape('points' => $points, 'tags' => $tags);
      }
    } else if (OsmUtils::isRailway($tags)) {
      $layers["layer_rail"][] = shape('points' => $points, 'tags' => $tags);
    } else if (OsmUtils::isBoundary($tags)) {
      $layers["layer_boundary"][] = shape(
        'points' => $points,
        'tags' => $tags,
      );
    } else {
      if (!OsmUtils::isPower($tags)) {
        $layers["layer_other"][] = shape(
          'points' => $points,
          'tags' => $tags,
        );
      }
    }

    $db->close();
    return true;
  }

  private async function genRender(
    Map<int, Map<string, Vector<OSMLayer>>> $all_layers,
    int $tile_level,
    OSMBoundingBox $bounding_box,
  ): Awaitable<Imagick> {
    $image_width = 1024;
    $image_height = 1024;

    $img = new Imagick();
    $img->newImage($image_width, $image_height, "none");
    $img->setImageFormat ("png");

    // $red = new ImagickDraw();
    // $red->setFillColor("#FF0000");
    // $red->rectangle(0.0, 0.0, 50.0, 200.0);
    //
    // $img->drawImage($red);
    //
    for ($layerIdx = -5; $layerIdx <= 5; $layerIdx++) {
      $layers = $all_layers[$layerIdx];

      $layer_land = $layers["layer_land"];
      foreach ($layer_land as $land) {
        await $this->genRenderLand($land, $img, $bounding_box);
      }
    }

    return $img;
  }

  private async function genRenderLand(
    OSMLayer $layer,
    Imagick $img,
    OSMBoundingBox $bounding_box,
  ): Awaitable<void> {

    $points = Vector {};
    foreach ($layer['points'] as $point) {
      $imagePoint = self::OsmPoint2ImagePoint(
        $point,
        $bounding_box,
        $img->getImageWidth(),
        $img->getImageHeight(),
      );

      $points[] = $imagePoint;
    }

    await self::genRenderPolygon($img, $points);
  }

  private static function OsmPoint2ImagePoint(
    OSMPoint $osmPoint,
    OSMBoundingBox $boundingBox,
    int $imageWidth,
    int $imageHeight,
  ): shape('x' => int, 'y' => int) {
    return shape(
      'x' =>
        (int) round(
          $imageWidth *
          ($osmPoint['longitude'] - $boundingBox['minlon']) /
          ($boundingBox['maxlon'] - $boundingBox['minlon']),
        ),
      'y' =>
        (int) round(
          $imageHeight -
          $imageHeight * ($osmPoint['latitude'] - $boundingBox['minlat']) /
          ($boundingBox['maxlat'] - $boundingBox['minlat']),
        ),
    );
  }

  private static async function genRenderPolygon(
    Imagick $img,
    Vector<shape('x' => int, 'y' => int)> $points,
  ): Awaitable<void> {
    $draw = new ImagickDraw();

    $draw->setStrokeOpacity(1.0);
    $draw->setStrokeColor('#FF0000');
    $draw->setStrokeWidth(2.0);
    $draw->setFillColor('#00FF00');

    // $points = [
    //   ['x' => 40 * 5, 'y' => 10 * 5],
    //   ['x' => 20 * 5, 'y' => 20 * 5],
    //   ['x' => 70 * 5, 'y' => 50 * 5],
    //   ['x' => 60 * 5, 'y' => 15 * 5],
    // ];

    $draw->polygon($points->toArray());

    $img->drawImage($draw);
  }
}
