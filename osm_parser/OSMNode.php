<?hh

type OSMNode = shape(
  'attributes' => Map<string, string>,
  'tags' => Vector<OSMTag>,
);
