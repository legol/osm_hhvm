-- select * from node
select id, visible, version, changeset, timestamp, user, uid, ST_X(wgs84long_lat) as lon, ST_Y(wgs84long_lat) as lat from node

-- select all node that belong to ways of a relation
select distinct way_nd.nd_ref as nd_ref from way_nd where way_nd.way_ref in (select relation_member.ref as way_ref from relation_member where relation_member.type='way' and relation_member.relation_ref=27700)

-- select all nodes that are referenced by a relation
select relation_member.ref as nd_ref from relation_member where relation_member.type='node' and relation_member.relation_ref=27700

-- select longtitude and latitude of all the nodes that belong to ways of a relation
select ST_X(node.wgs84long_lat) as lon, ST_Y(node.wgs84long_lat) as lat from node
		right join
				(select distinct way_nd.nd_ref as nd_ref from way_nd where way_nd.way_ref in
					(select relation_member.ref as way_ref from relation_member where relation_member.type='way' and relation_member.relation_ref=27700)) as nodes
		on nodes.nd_ref=node.id

-- select longtitude and latitude of all nodes that are referenced by a relation
select ST_X(node.wgs84long_lat) as lon, ST_Y(node.wgs84long_lat) as lat from node
	right join
		(select relation_member.ref as nd_ref from relation_member where relation_member.type='node' and relation_member.relation_ref=27700) as nodes
	on nodes.nd_ref=node.id


-- select all relation, aka relation_ref2, that are referenced by this relation
select relation_member.ref as relation_ref2 from relation_member where relation_member.type='relation' and relation_member.relation_ref=1350622;

latitude is y
longitude is x

-- select all non top level relations
select distinct ref as relation_ref2 from relation_member where type='relation'

-- select all top level relations
select relation.id as relation_ref from relation where relation.id not in (select distinct relation_member.ref as relation_ref2 from relation_member where relation_member.type='relation' )

-- insert into top level relation table
insert into top_level_relation(relation_ref) select relation.id as relation_ref from relation where relation.id not in (select distinct relation_member.ref as relation_ref2 from relation_member where relation_member.type='relation' )


-- select all relation that intersects with boundingBox
select * from relation_bounding_box where not(boundingBox.maxlat < minlat or boundingBox.maxlon < minlon or maxlat < boundingBox.minlat maxlon < boundingBox.minlon)

-- select all top level relations whose bounding box intersects with boundingBox
select relation_ref from relation_bounding_box where not(boundingBox.maxlat < minlat or boundingBox.maxlon < minlon or maxlat < boundingBox.minlat or maxlon < boundingBox.minlon) and
 relation_ref in (select relation_ref from top_level_relation)

select relation_ref from relation_bounding_box where not(40.112 < minlat or 116.344 < minlon or maxlat < 40.086 or maxlon < 116.268) and  relation_ref in (select relation_ref from top_level_relation)

-- select all ways whose bounding box intersects with boundingBox
select way_ref from way_bounding_box where not(boundingBox.maxlat < minlat or boundingBox.maxlon < minlon or maxlat < boundingBox.minlat or maxlon < boundingBox.minlon)
select way_ref from way_bounding_box where not(40.112 < minlat or 116.344 < minlon or maxlat < 40.086 or maxlon < 116.268)

-- only select visible ways whose bounding box intersects with boundingBox
select way_ref from way_bounding_box
 left join
(select way.id from way where way.visible=true) as way
on way_bounding_box.way_ref=way.id
where not(40.112 < minlat or 116.344 < minlon or maxlat < 40.086 or maxlon < 116.268)


-- get geom points of a way
select node.id as nd_ref, ST_AsGeoJson(wgs84long_lat) as point_json from node right join (select * from way_nd where way_ref=24797748) as way_nodes
on
node.id=way_nodes.nd_ref

-- lod 5, no building
select way_ref from way_bounding_box where not(40.0997 < minlat or 116.3158 < minlon or maxlat < 40.0785 or maxlon < 116.2866) and
	way_ref not in(select way_ref from way_tag where k='building' )

-- lod 10
-- no building, no noname tertiary highway
select way_ref from way_bounding_box where not(40.0997 < minlat or 116.3158 < minlon or maxlat < 40.0785 or maxlon < 116.2866) and
(way_ref not in (select way_ref from way_tag where (k='building' or
	(k='highway' and v='cycleway') or
	(k='highway' and v='footway') or
	(k='highway' and v='residential') or
	(k='highway' and v='service') or
	(k='highway' and v='unclassified'))))
and
(way_ref not in (select way_ref from way_tag where (k='highway' and v='tertiary') and
	way_ref not in (select way_ref from way_tag where k='name' and
		way_ref in (select way_ref from way_tag where (k='highway' and v='tertiary')))))



-- select all landuse with coordinates
select way_ref, nd_ref, wgs84long_lat from
	(select way_ref, nd_ref from way_nd where way_ref in (select way_ref from way_tag where k='landuse')) as landuse
left join node
on landuse.nd_ref=node.id

-- all center point of landuse
select way_ref, ST_Centroid(ST_MakeLine(wgs84long_lat)) from
	(select way_ref, nd_ref from way_nd where way_ref in (select way_ref from way_tag where k='landuse')) as landuse
left join node
on landuse.nd_ref=node.id
group by way_ref


-- indexed way bounding box
insert into way_bounding_box(way_ref, minlon, minlat, maxlon, maxlat, wgs84_bounding_box) values (4231222, 1, 2, 3, 4, ST_SetSRID(ST_MakeLine(ST_MakePoint(1, 4), ST_MakePoint(3, 2)), 4326))

-- indexed query, much faster
select relation_ref, minlat, minlon, maxlat, maxlon, st_astext(wgs84_bounding_box) from relation_bounding_box where wgs84_bounding_box && ST_SetSRID(ST_MakeLine(ST_MakePoint(116.3731852, 39.8752726), ST_MakePoint(116.380653, 39.8705841)), 4326)

-- select bounding box of way
select way_ref, minlat, minlon, maxlat, maxlon, st_astext(wgs84_bounding_box) from way_bounding_box limit 10;
