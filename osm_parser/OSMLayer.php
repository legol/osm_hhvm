<?hh

type OSMLayer = shape (
  'points' => Vector<OSMPoint>,
  'tags' => Map<string, string>,
);
