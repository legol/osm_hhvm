<?hh

enum OSMTypes: string as string {
  NODE = 'node';
  WAY = 'way';
  RELATION = 'relation';
}

type OSMTag = shape(
  'k' => string,
  'v' => string,
);

type OSMNode = shape(
  'attributes' => Map<string, string>,
  'tags' => Vector<OSMTag>,
);

type OSMWay = shape(
  'attributes' => Map<string, string>,
  'tags' => Vector<OSMTag>,
  'nodes' => Vector<int>,
);

type OSMRelationMember = shape(
  'ref' => int,
  'type' => string,
  'role' => string,
);

type OSMRelation = shape(
  'attributes' => Map<string, string>,
  'tags' => Vector<OSMTag>,
  'members' => Vector<OSMRelationMember>,
);

type OSMPoint = shape(
  'node_id' => int,
  'latitude' => float,
  'longitude' => float,
);

type OSMBoundingBox = shape(
  'minlat' => float,
  'maxlat' => float,
  'minlon' => float,
  'maxlon' => float,
);
