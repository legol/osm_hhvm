<?hh

type OSMWay = shape(
  'attributes' => Map<string, string>,
  'tags' => Vector<OSMTag>,
  'nodes' => Vector<int>,
);
