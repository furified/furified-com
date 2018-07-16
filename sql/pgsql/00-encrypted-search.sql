/* Centralized index table for encrypted search */
CREATE TABLE IF NOT EXISTS furified_search_index (
  indexid BIGSERIAL PRIMARY KEY,
  type TEXT,
  value TEXT,
  reference BIGINT
);
CREATE INDEX ON furified_search_index(type);
CREATE INDEX ON furified_search_index(value);
CREATE INDEX ON furified_search_index(reference);
CREATE UNIQUE INDEX ON furified_search_index(type, value, reference);
