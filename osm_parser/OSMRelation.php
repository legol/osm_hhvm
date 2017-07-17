<?hh

type OSMRelation = shape(
  'attributes' => Map<string, string>,
  'tags' => Vector<OSMTag>,
  'members' => Vector<OSMRelationMember>,
);
