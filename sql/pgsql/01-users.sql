/* User accounts definition */
CREATE TABLE IF NOT EXISTS furified_users (
  userid BIGSERIAL PRIMARY KEY,
  username TEXT,
  displayname TEXT,
  email TEXT,
  pwhash TEXT,
  twofactor TEXT,
  gpgfingerprint TEXT,
  active BOOLEAN,
  created TIMESTAMP DEFAULT NOW(),
  modified TIMESTAMP DEFAULT NOW()
);

CREATE INDEX ON furified_users(username);
CREATE INDEX ON furified_users(email);
CREATE INDEX ON furified_users(displayname);

/* Roles (RBAC) */
CREATE TABLE IF NOT EXISTS furified_roles (
  roleid BIGSERIAL PRIMARY KEY,
  parent BIGINT NULL REFERENCES furified_roles(roleid),
  name TEXT,
  created TIMESTAMP DEFAULT NOW(),
  modified TIMESTAMP DEFAULT NOW()
);

/* User-role memberships */
CREATE TABLE IF NOT EXISTS furified_users_roles(
  userid BIGINT REFERENCES furified_users(userid),
  roleid BIGINT REFERENCES furified_roles(roleid),
  created TIMESTAMP DEFAULT NOW()
);

/* Profile information */
CREATE TABLE IF NOT EXISTS furified_users_profile(
  userid BIGINT REFERENCES furified_users(userid),
  biography TEXT,
  socialmedia JSONB,
  created TIMESTAMP DEFAULT NOW(),
  modified TIMESTAMP DEFAULT NOW()
);
CREATE UNIQUE INDEX ON furified_users_profile(userid);

/* Create indexes on popular social media options: */
CREATE INDEX ON furified_users_profile USING BTREE ((socialmedia->>'amino'));
CREATE INDEX ON furified_users_profile USING BTREE ((socialmedia->>'e621'));
CREATE INDEX ON furified_users_profile USING BTREE ((socialmedia->>'f-list'));
CREATE INDEX ON furified_users_profile USING BTREE ((socialmedia->>'furaffinity'));
CREATE INDEX ON furified_users_profile USING BTREE ((socialmedia->>'furry-network'));
CREATE INDEX ON furified_users_profile USING BTREE ((socialmedia->>'inkbunny'));
CREATE INDEX ON furified_users_profile USING BTREE ((socialmedia->>'reddit'));
CREATE INDEX ON furified_users_profile USING BTREE ((socialmedia->>'twitter'));
CREATE INDEX ON furified_users_profile USING BTREE ((socialmedia->>'twitter-after-dark'));
CREATE INDEX ON furified_users_profile USING BTREE ((socialmedia->>'telegram'));
CREATE INDEX ON furified_users_profile USING BTREE ((socialmedia->>'telegram-after-dark'));
