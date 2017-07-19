<?hh // strict

// require_once 'PostgresqlAdapter.php';

class OsmTileUtils {

  private static float $EarthRadius = 6378137.0;
  private static float $MinLatitude = -85.05112878;
  private static float $MaxLatitude = 85.05112878;
  private static float $MinLongitude = -180.0;
  private static float $MaxLongitude = 180.0;


  /// <summary>
  /// Clips a number to the specified minimum and maximum values.
  /// </summary>
  /// <param name="n">The number to clip.</param>
  /// <param name="minValue">Minimum allowable value.</param>
  /// <param name="maxValue">Maximum allowable value.</param>
  /// <returns>The clipped value.</returns>
  private static function clip(
    float $n,
    float $minValue,
    float $maxValue,
  ): float {
    return min(max($n, $minValue), $maxValue);
  }

  /// <summary>
  /// Determines the map width and height (in pixels) at a specified level
  /// of detail.
  /// </summary>
  /// <param name="levelOfDetail">Level of detail, from 1 (lowest detail)
  /// to 23 (highest detail).</param>
  /// <returns>The map width and height in pixels.</returns>
  public static function getMapSize(int $levelOfDetail): int {
      return (int) 256 << $levelOfDetail;
  }



  /// <summary>
  /// Determines the ground resolution (in meters per pixel) at a specified
  /// latitude and level of detail.
  /// </summary>
  /// <param name="latitude">Latitude (in degrees) at which to measure the
  /// ground resolution.</param>
  /// <param name="levelOfDetail">Level of detail, from 1 (lowest detail)
  /// to 23 (highest detail).</param>
  /// <returns>The ground resolution, in meters per pixel.</returns>
  public static function getGroundResolution(
    float $latitude,
    int $levelOfDetail,
  ): float {
    $latitude = self::clip($latitude, self::$MinLatitude, self::$MaxLatitude);
    return cos($latitude * M_PI / 180) * 2 * M_PI * self::$EarthRadius / self::getMapSize($levelOfDetail);
  }

  /// <summary>
  /// Determines the map scale at a specified latitude, level of detail,
  /// and screen resolution.
  /// </summary>
  /// <param name="latitude">Latitude (in degrees) at which to measure the
  /// map scale.</param>
  /// <param name="levelOfDetail">Level of detail, from 1 (lowest detail)
  /// to 23 (highest detail).</param>
  /// <param name="screenDpi">Resolution of the screen, in dots per inch.</param>
  /// <returns>The map scale, expressed as the denominator N of the ratio 1 : N.</returns>
  public static function getMapScale(
    float $latitude,
    int $levelOfDetail,
    int $screenDpi,
  ): float {
    return self::getGroundResolution($latitude, $levelOfDetail) * $screenDpi / 0.0254;
  }

  /// <summary>
  /// Converts a point from latitude/longitude WGS-84 coordinates (in degrees)
  /// into pixel XY coordinates at a specified level of detail.
  /// </summary>
  /// <param name="latitude">Latitude of the point, in degrees.</param>
  /// <param name="longitude">Longitude of the point, in degrees.</param>
  /// <param name="levelOfDetail">Level of detail, from 1 (lowest detail)
  /// to 23 (highest detail).</param>
  /// <param name="pixelX">Output parameter receiving the X coordinate in pixels.</param>
  /// <param name="pixelY">Output parameter receiving the Y coordinate in pixels.</param>
  public static function latLongToPixelXY(
    float $latitude,
    float $longitude,
    int $levelOfDetail,
  ): shape(
    'pixelX' => int,
    'pixelY' =>int,
  ) {
    $latitude = self::clip($latitude, self::$MinLatitude, self::$MaxLatitude);
    $longitude = self::clip($longitude, self::$MinLongitude, self::$MaxLongitude);

    $x = ($longitude + 180) / 360;
    $sinLatitude = sin($latitude * M_PI / 180);
    $y = 0.5 - log((1 + $sinLatitude) / (1 - $sinLatitude)) / (4 * M_PI);

    $mapSize = self::getMapSize($levelOfDetail);
    $pixelX = (int) self::clip($x * $mapSize + 0.5, 0.0, (float)$mapSize - 1);
    $pixelY = (int) self::clip($y * $mapSize + 0.5, 0.0, (float)$mapSize - 1);

    return shape('pixelX' => $pixelX, 'pixelY' => $pixelY);
  }

  /// <summary>
  /// Converts a pixel from pixel XY coordinates at a specified level of detail
  /// into latitude/longitude WGS-84 coordinates (in degrees).
  /// </summary>
  /// <param name="pixelX">X coordinate of the point, in pixels.</param>
  /// <param name="pixelY">Y coordinates of the point, in pixels.</param>
  /// <param name="levelOfDetail">Level of detail, from 1 (lowest detail)
  /// to 23 (highest detail).</param>
  /// <param name="latitude">Output parameter receiving the latitude in degrees.</param>
  /// <param name="longitude">Output parameter receiving the longitude in degrees.</param>
  public static function pixelXYToLatLong(
    int $pixelX,
    int $pixelY,
    int $levelOfDetail,
  ): shape('latitude' => float, 'longitude' => float) {
    $mapSize = (float)self::getMapSize($levelOfDetail);
    $x = (self::clip((float)$pixelX, 0.0, $mapSize - 1) / $mapSize) - 0.5;
    $y = 0.5 - (self::clip((float)$pixelY, 0.0, $mapSize - 1) / $mapSize);

    $latitude = 90 - 360 * atan(exp(-$y * 2 * M_PI)) / M_PI;
    $longitude = 360 * $x;

    return shape('latitude' => $latitude, 'longitude' => $longitude);
  }



  /// <summary>
  /// Converts pixel XY coordinates into tile XY coordinates of the tile containing
  /// the specified pixel.
  /// </summary>
  /// <param name="pixelX">Pixel X coordinate.</param>
  /// <param name="pixelY">Pixel Y coordinate.</param>
  /// <param name="tileX">Output parameter receiving the tile X coordinate.</param>
  /// <param name="tileY">Output parameter receiving the tile Y coordinate.</param>
  public static function pixelXYToTileXY(
    int $pixelX,
    int $pixelY,
  ): shape('tileX' => int, 'tileY' => int) {
    $tileX = $pixelX / 256;
    $tileY = $pixelY / 256;

    return shape('tileX' => (int)$tileX, 'tileY' => (int)$tileY);
  }



  /// <summary>
  /// Converts tile XY coordinates into pixel XY coordinates of the upper-left pixel
  /// of the specified tile.
  /// </summary>
  /// <param name="tileX">Tile X coordinate.</param>
  /// <param name="tileY">Tile Y coordinate.</param>
  /// <param name="pixelX">Output parameter receiving the pixel X coordinate.</param>
  /// <param name="pixelY">Output parameter receiving the pixel Y coordinate.</param>
  public static function tileXYToPixelXY(
    int $tileX,
    int $tileY,
  ): shape('pixelX' => int, 'pixelY' => int) {
    $pixelX = $tileX * 256;
    $pixelY = $tileY * 256;

    return shape('pixelX' => $pixelX, 'pixelY' => $pixelY);
  }

  /// <summary>
  /// Converts tile XY coordinates into a QuadKey at a specified level of detail.
  /// </summary>
  /// <param name="tileX">Tile X coordinate.</param>
  /// <param name="tileY">Tile Y coordinate.</param>
  /// <param name="levelOfDetail">Level of detail, from 1 (lowest detail)
  /// to 23 (highest detail).</param>
  /// <returns>A string containing the QuadKey.</returns>
  public static function tileXYToQuadKey(
    int $tileX,
    int $tileY,
    int $levelOfDetail,
  ): string {
    $quadKey = '';
    for ($i = $levelOfDetail; $i > 0; $i--) {
      $digit = 0;
      $mask = 1 << ($i - 1);
      if (($tileX & $mask) != 0) {
        $digit++;
      }
      if (($tileY & $mask) != 0) {
        $digit++;
        $digit++;
      }
      $quadKey .= $digit;
    }

    return $quadKey;
  }



  /// <summary>
  /// Converts a QuadKey into tile XY coordinates.
  /// </summary>
  /// <param name="quadKey">QuadKey of the tile.</param>
  /// <param name="tileX">Output parameter receiving the tile X coordinate.</param>
  /// <param name="tileY">Output parameter receiving the tile Y coordinate.</param>
  /// <param name="levelOfDetail">Output parameter receiving the level of detail.</param>
  public static function quadKeyToTileXY(
    string $quadKey,
  ): ?shape(
    'tileX' => int,
    'tileY' => int,
    'level' => int,
  ) {
    $tileX = $tileY = 0;
    $levelOfDetail = strlen($quadKey);
    for ($i = $levelOfDetail; $i > 0; $i--) {
      $mask = 1 << ($i - 1);
      switch ($quadKey[$levelOfDetail - $i]) {
        case '0':
          break;

        case '1':
          $tileX |= $mask;
          break;

        case '2':
          $tileY |= $mask;
          break;

        case '3':
          $tileX |= $mask;
          $tileY |= $mask;
          break;

        default:
          // throw new ArgumentException("Invalid QuadKey digit sequence.");
          return null;
      }
    }

    return shape(
      'tileX' => $tileX,
      'tileY' => $tileY,
      'level' => $levelOfDetail,
    );
  }

  /// Converts latitude/longitude into tile XY
  public static function latLongToTileXY(
    float $latitude,
    float $longitude,
    int $tile_level,
  ): shape(
    'tileX' => int,
    'tileY' => int,
  ) {
    $pixel_XY =
      OsmTileUtils::latLongToPixelXY($latitude, $longitude, $tile_level);
    return
      OsmTileUtils::pixelXYToTileXY($pixel_XY['pixelX'], $pixel_XY['pixelY']);
  }

  /// Get the bounding box of a tile
  public static function tileBoundingBox(
    int $tile_x,
    int $tile_y,
    int $tile_level,
  ): OSMBoundingBox {
    $left_top_pixel_XY =
      OsmTileUtils::tileXYToPixelXY($tile_x, $tile_y);
    $bot_right_pixel_XY = shape(
      'pixelX' => $left_top_pixel_XY['pixelX'] + 256,
      'pixelY' => $left_top_pixel_XY['pixelY'] + 256,
    );
    $top_left_lat_lon = OsmTileUtils::pixelXYToLatLong(
      $left_top_pixel_XY['pixelX'],
      $left_top_pixel_XY['pixelY'],
      $tile_level,
    );
    $bot_right_lat_lon = OsmTileUtils::pixelXYToLatLong(
      $bot_right_pixel_XY['pixelX'],
      $bot_right_pixel_XY['pixelY'],
      $tile_level,
    );

    return shape(
      'minlat' => $top_left_lat_lon['latitude'],
      'maxlat' => $bot_right_lat_lon['latitude'],
      'minlon' => $top_left_lat_lon['longitude'],
      'maxlon' => $bot_right_lat_lon['longitude'],
    );
  }

  /// Converts latitude/longitude into tile top left latitude/longitude and
  /// bot right latitude/longitude
  public static function latLongToTileBoundingBox(
    float $latitude,
    float $longitude,
    int $tile_level,
  ): OSMBoundingBox {
    $pixel_XY =
      OsmTileUtils::latLongToPixelXY($latitude, $longitude, $tile_level);
    $tile_XY =
      OsmTileUtils::pixelXYToTileXY($pixel_XY['pixelX'], $pixel_XY['pixelY']);
    $left_top_pixel_XY =
      OsmTileUtils::tileXYToPixelXY($tile_XY['tileX'], $tile_XY['tileY']);
    $bot_right_pixel_XY = shape(
      'pixelX' => $left_top_pixel_XY['pixelX'] + 256,
      'pixelY' => $left_top_pixel_XY['pixelY'] + 256,
    );
    $top_left_lat_lon = OsmTileUtils::pixelXYToLatLong(
      $left_top_pixel_XY['pixelX'],
      $left_top_pixel_XY['pixelY'],
      $tile_level,
    );
    $bot_right_lat_lon = OsmTileUtils::pixelXYToLatLong(
      $bot_right_pixel_XY['pixelX'],
      $bot_right_pixel_XY['pixelY'],
      $tile_level,
    );

    return shape(
      'minlat' => $top_left_lat_lon['latitude'],
      'maxlat' => $bot_right_lat_lon['latitude'],
      'minlon' => $top_left_lat_lon['longitude'],
      'maxlon' => $bot_right_lat_lon['longitude'],
    );
  }
}
