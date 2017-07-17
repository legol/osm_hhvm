<?hh

class OsmUtils {
  public static function isBuilding(Map<string, string> $tags): bool {
    return $tags->containsKey('building');
  }

  public static function isLand(Map<string, string> $tags): bool {
    return $tags->containsKey('landuse');
  }

  public static function isNetural(Map<string, string> $tags): bool {
    return $tags->containsKey('natural');
  }

  public static function isHighway(Map<string, string> $tags): bool {
    return $tags->containsKey('highway');
  }

  public static function isWaterway(Map<string, string> $tags): bool {
    return $tags->containsKey('waterway');
  }

  public static function isLeisure(Map<string, string> $tags): bool {
    return $tags->containsKey('leisure');
  }

  public static function isAmenity(Map<string, string> $tags): bool {
    return $tags->containsKey('amenity');
  }

  public static function isBoundary(Map<string, string> $tags): bool {
    return $tags->containsKey('boundary');
  }

  public static function isRailway(Map<string, string> $tags): bool {
    return $tags->containsKey('railway');
  }

  public static function isPower(Map<string, string> $tags): bool {
    return $tags->containsKey('power');
  }
}
