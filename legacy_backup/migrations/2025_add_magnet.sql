-- Migration: add magnet_link column to movies table
ALTER TABLE movies
  ADD COLUMN magnet_link TEXT NULL AFTER video;

-- Optional: add an index for faster lookup
CREATE INDEX idx_movies_magnet ON movies (magnet_link(32));