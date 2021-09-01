-- ##############################################
-- KURS: dt161g
-- Projekt
-- Mattias Lindell
-- Create table called member
-- Create table called yyy
-- etc...................................
-- ##############################################

-- ##############################################
-- First we create the schema
-- ##############################################

DROP SCHEMA IF EXISTS dt161g_project CASCADE;

CREATE SCHEMA dt161g_project;

-- ##############################################
-- First we create the member table
-- ##############################################
DROP TABLE IF EXISTS dt161g_project.member;

CREATE TABLE dt161g_project.member (
  id        SERIAL PRIMARY KEY,
  username  text NOT NULL CHECK (username <> ''),
  password  text NOT NULL CHECK (password  <> ''),
  CONSTRAINT unique_user UNIQUE(username)
)
WITHOUT OIDS;

-- ##############################################
-- Now we insert some values
-- ##############################################
INSERT INTO dt161g_project.member (username, password) VALUES ('m', md5('mm'));
INSERT INTO dt161g_project.member (username, password) VALUES ('a', md5('aa'));

-- ##############################################
-- Then we create the role table
-- ##############################################
DROP TABLE IF EXISTS dt161g_project.role;

CREATE TABLE dt161g_project.role (
  id        SERIAL PRIMARY KEY,
  role      text NOT NULL CHECK (role <> ''),
  CONSTRAINT unique_role UNIQUE(role)
)
WITHOUT OIDS;

-- ##############################################
-- Now we insert some values
-- ##############################################
INSERT INTO dt161g_project.role (role) VALUES ('member');
INSERT INTO dt161g_project.role (role) VALUES ('admin');

-- ##############################################
-- Then we create the member_role table
-- ##############################################
DROP TABLE IF EXISTS dt161g_project.member_role;

CREATE TABLE dt161g_project.member_role (
  id        SERIAL PRIMARY KEY,
  member_id integer REFERENCES dt161g_project.member (id) ON DELETE CASCADE,
  role_id   integer REFERENCES dt161g_project.role (id),
  CONSTRAINT unique_member_role UNIQUE(member_id, role_id)
)
WITHOUT OIDS;

-- ##############################################
-- Now we insert some values
-- ##############################################
INSERT INTO dt161g_project.member_role (member_id, role_id) VALUES (1,1);
INSERT INTO dt161g_project.member_role (member_id, role_id) VALUES (2,1);
INSERT INTO dt161g_project.member_role (member_id, role_id) VALUES (2,2);

-- ##############################################
-- Then we create the image table
-- ##############################################
DROP TABLE IF EXISTS dt161g_project.image;

CREATE TABLE dt161g_project.image (
  id        SERIAL PRIMARY KEY,
  name  text NOT NULL CHECK (name <> ''),
  filetype  text NOT NULL CHECK (filetype <> ''),
  time  text NOT NULL CHECK (time <> ''),
  content  text NOT NULL CHECK (content <> ''),
  checksum  text NOT NULL CHECK (checksum <> ''),
  CONSTRAINT unique_image UNIQUE(checksum)
)
WITHOUT OIDS;

-- ##############################################
-- Then we create the category table
-- ##############################################
DROP TABLE IF EXISTS dt161g_project.category;

CREATE TABLE dt161g_project.category (
  id        SERIAL PRIMARY KEY,
  category  text NOT NULL CHECK (category <> ''),
  CONSTRAINT unique_category UNIQUE(category)
)
WITHOUT OIDS;

-- ##############################################
-- Now we insert some values
-- ##############################################
INSERT INTO dt161g_project.category (category) VALUES ('dogs');
INSERT INTO dt161g_project.category (category) VALUES ('cats');

-- ##############################################
-- Then we create the member_image_category table
-- ##############################################
DROP TABLE IF EXISTS dt161g_project.member_image_category;

CREATE TABLE dt161g_project.member_image_category (
  id        SERIAL PRIMARY KEY,
  image_id integer REFERENCES dt161g_project.image (id) ON DELETE CASCADE,
  category_id 	integer REFERENCES dt161g_project.category (id),
   member_id integer REFERENCES dt161g_project.member (id),
  CONSTRAINT unique_member_image_category UNIQUE(image_id, category_id, member_id)
)
WITHOUT OIDS;

-- ##############################################
-- Then we create the member_category table
-- ##############################################
DROP TABLE IF EXISTS dt161g_project.member_category;

CREATE TABLE dt161g_project.member_category (
  id        SERIAL PRIMARY KEY,
  member_id integer REFERENCES dt161g_project.member (id) ON DELETE CASCADE,
  category_id 	integer REFERENCES dt161g_project.category (id),
  CONSTRAINT unique_member_category UNIQUE(member_id, category_id)
)
WITHOUT OIDS;

-- ##############################################
-- Now we insert some values
-- ##############################################
INSERT INTO dt161g_project.member_category (member_id, category_id) VALUES (1, 1);
INSERT INTO dt161g_project.member_category (member_id, category_id) VALUES (1, 2);
INSERT INTO dt161g_project.member_category (member_id, category_id) VALUES (2, 1);
INSERT INTO dt161g_project.member_category (member_id, category_id) VALUES (2, 2);