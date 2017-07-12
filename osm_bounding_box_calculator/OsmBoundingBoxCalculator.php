<?hh

class OsmBoundingBoxCalculator {
  public static function create(): OsmBoundingBoxCalculator {
    return new OsmBoundingBoxCalculator();
  }

  public async function genCalculateWayBoundingBox(): Awaitable<bool> {
    $db = PostgresqlAdapter::create();
    if ($db->connect() === false) {
      return false;
    }

    $offset = 0;
    while(true) {
      $ways = await $db->genAllWays($offset);
      if ($ways === null) {
        return false;
      }

      if ($ways->isEmpty()) {
        break;
      }

      foreach ($ways as $way) {
        $points = await $db->genPointsOfWay($way);
        if ($points === null) {
          return false;
        }

        $saved = await $db->genSaveWayBoundingBox(
          $way,
          self::calcBoundingBox($points),
        );
        if ($saved === false) {
          return false;
        }
      }

      $offset += $ways->count();
    }

    printf("total ways:%d\n", $offset);

    $db->close();
    return true;
  }

  private static function calcBoundingBox(
    Vector<OSMPoint> $points,
  ): OSMBoundingBox {
    $minlat = 10000.0;
    $maxlat = -10000.0;
    $minlon = 10000.0;
    $maxlon = -10000.0;

    foreach ($points as $point) {
      if ($point['latitude'] > $maxlat) {
        $maxlat = $point['latitude'];
      }
      if ($point['latitude'] < $minlat) {
        $minlat = $point['latitude'];
      }
      if ($point['longitude'] > $maxlon) {
        $maxlon = $point['longitude'];
      }
      if ($point['longitude'] < $minlon) {
        $minlon = $point['longitude'];
      }
    }

    return shape(
      'minlat' => $minlat,
      'maxlat' => $maxlat,
      'minlon' => $minlon,
      'maxlon' => $maxlon,
    );
  }

}
