<?hh

// require_once 'PostgresqlAdapter.php';

class OsmTileGenerator {
  public static function create(): OsmTileGenerator {
    return new OsmTileGenerator();
  }

  public async function gen(
    int $tile_x,
    int $tile_y,
    int $tile_l,
  ): Awaitable<bool> {
    printf("Generating (%d, %d):%d\n", $tile_x, $tile_y, $tile_l);
    printf("Done...\n");
    return true;
  }
}
