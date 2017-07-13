<?hh

// require_once 'PostgresqlAdapter.php';

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

    $lat = (float)$matched[1];
    $lon = (float)$matched[2];
    $level = (int)$matched[3];

    $img = new Imagick();
    $img->newImage(256, 256, "none");
    $img->setImageFormat ("png");


    $red = new ImagickDraw();
    $red->setFillColor("#FF0000");
    $red->rectangle(0.0, 0.0, 50.0, 200.0);

    $img->drawImage($red);

    return $img;
  }
}
