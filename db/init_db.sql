DROP TABLE IF EXISTS "relation_bounding_box";
DROP TABLE IF EXISTS "way_bounding_box";
DROP TABLE IF EXISTS "top_level_relation";

DROP INDEX IF EXISTS "way_bounding_box_idx";
DROP INDEX IF EXISTS "relation_bounding_box_idx";

DROP INDEX IF EXISTS "relation_member_relation_ref";
DROP INDEX IF EXISTS "relation_tag_relation_ref";
DROP INDEX IF EXISTS "relation_member_type";
DROP INDEX IF EXISTS "relation_member_type_ref";
DROP TABLE IF EXISTS "relation_tag";
DROP TABLE IF EXISTS "relation_member";
DROP TABLE IF EXISTS "relation";


DROP INDEX IF EXISTS "way_tag_way_ref";
DROP INDEX IF EXISTS "way_nd_way_ref";
DROP INDEX IF EXISTS "way_nd_nd_ref";
DROP TABLE IF EXISTS "way_tag";
DROP TABLE IF EXISTS "way_nd";
DROP TABLE IF EXISTS "way";

DROP INDEX IF EXISTS "node_tag_nd_ref";
DROP TABLE IF EXISTS "node_tag";
DROP TABLE IF EXISTS "node";




CREATE TABLE "node"(
	"id" bigint NOT NULL,
	"visible" boolean,
	"version" bigint,
	"changeset" bigint,
	"timestamp" timestamp with time zone,
	"user" varchar(100),
	"uid" bigint,
	"wgs84long_lat" geometry NOT NULL,
	PRIMARY KEY("id")
);

CREATE TABLE "node_tag"(
	"nd_ref" bigint NOT NULL,
	"k" varchar(256),
	"v" varchar(256)
	--FOREIGN KEY ("nd_ref") REFERENCES "node"("id") ON DELETE CASCADE
);

CREATE INDEX "node_tag_nd_ref" ON "node_tag"("nd_ref");



CREATE TABLE "way"(
	"id" bigint NOT NULL,
	"visible" boolean,
	"version" bigint,
	"changeset" bigint,
	"timestamp" timestamp with time zone,
	"user" varchar(100),
	"uid" bigint,
	PRIMARY KEY("id")
);

CREATE TABLE "way_nd"(
	"way_ref" bigint NOT NULL,
	"nd_ref" bigint NOT NULL,
	"order" bigint NOT NULL
	--FOREIGN KEY ("way_ref") REFERENCES "way"("id") ON DELETE CASCADE,
	--FOREIGN KEY ("nd_ref") REFERENCES "node"("id") ON DELETE CASCADE
);
CREATE INDEX "way_nd_way_ref" ON "way_nd"("way_ref");
CREATE INDEX "way_nd_nd_ref" ON "way_nd"("nd_ref");



CREATE TABLE "way_tag"(
	"way_ref" bigint NOT NULL,
	"k" varchar(256),
	"v" varchar(256)
	--FOREIGN KEY ("way_ref") REFERENCES "way"("id") ON DELETE CASCADE
);
CREATE INDEX "way_tag_way_ref" ON "way_tag"("way_ref");






CREATE TABLE "relation"(
	"id" bigint NOT NULL,
	"visible" boolean,
	"version" bigint,
	"changeset" bigint,
	"timestamp" timestamp with time zone,
	"user" varchar(100),
	"uid" bigint,
	PRIMARY KEY("id")
);

CREATE TABLE "relation_member"(
	"relation_ref" bigint NOT NULL,
	"type" varchar(10) NOT NULL,
	"ref" bigint NOT NULL,
	"role" varchar(256),
	"order" bigint NOT NULL
);
CREATE INDEX "relation_member_relation_ref" ON "relation_member"("relation_ref");
CREATE INDEX "relation_member_type" ON "relation_member"("type");
CREATE INDEX "relation_member_type_ref" ON "relation_member"("type", "ref");


CREATE TABLE "relation_tag"(
	"relation_ref" bigint NOT NULL,
	"k" varchar(256),
	"v" varchar(256)
	--FOREIGN KEY ("relation_ref") REFERENCES "relation"("id") ON DELETE CASCADE
);
CREATE INDEX "relation_tag_relation_ref" ON "relation_tag"("relation_ref");


CREATE TABLE "way_bounding_box"(
	"way_ref" bigint NOT NULL,
	"minlat" float(32),
	"minlon" float(32),
	"maxlat" float(32),
	"maxlon" float(32),
	"wgs84_bounding_box" geometry NOT NULL,
	PRIMARY KEY("way_ref")
	--FOREIGN KEY ("way_ref") REFERENCES "way"("id") ON DELETE CASCADE
);
CREATE INDEX "way_bounding_box_idx" ON "way_bounding_box" USING GIST ("wgs84_bounding_box");


CREATE TABLE "relation_bounding_box"(
	"relation_ref" bigint NOT NULL,
	"minlat" float(32),
	"minlon" float(32),
	"maxlat" float(32),
	"maxlon" float(32),
	"wgs84_bounding_box" geometry NOT NULL,
	PRIMARY KEY("relation_ref")
	--FOREIGN KEY ("relation_ref") REFERENCES "relation"("id") ON DELETE CASCADE
);
CREATE INDEX "relation_bounding_box_idx" ON "relation_bounding_box" USING GIST ("wgs84_bounding_box");


CREATE TABLE "top_level_relation"(
	"relation_ref" bigint NOT NULL,
	PRIMARY KEY("relation_ref")
	--FOREIGN KEY ("relation_ref") REFERENCES "relation"("id") ON DELETE CASCADE
);
