-- MovieStream Testing Data
-- Run this to populate your database with test data

-- Add sample notifications for user ID 1 (change the user_id to match your logged-in user)
INSERT INTO notifications (user_id, type, title, message, link, is_read) VALUES
(1, 'welcome', 'Welcome to MovieStream!', 'Thank you for joining us. Explore our vast collection of movies and shows!', '/', 0),
(1, 'movie', 'New Movies Added', 'Check out 5 new blockbusters added this week!', '/movies', 0),
(1, 'system', 'Feature Update', 'We have added advanced search with genre and year filtering!', null, 0),
(1, 'movie', 'Trending Now', 'The Dark Knight is trending today. Watch it now!', '/movies/1', 1),
(1, 'system', 'Maintenance Notice', 'Scheduled maintenance on Sunday at 2:00 AM EST.', null, 1);

-- Mark some movies as premium (adjust IDs based on your movies)
UPDATE movies SET is_premium = 1 WHERE id IN (1, 3, 5);

-- Add some sample movies with different genres and years (if you don't have many)
INSERT INTO movies (title, description, genre, year, poster, video_url, actor, director, rating) VALUES
('Inception', 'A thief who steals corporate secrets through dream-sharing technology.', 'Sci-Fi', 2010, 'inception.jpg', 'https://example.com/inception.mp4', 'Leonardo DiCaprio', 'Christopher Nolan', 8.8),
('The Shawshank Redemption', 'Two imprisoned men bond over years, finding redemption.', 'Drama', 1994, 'shawshank.jpg', 'https://example.com/shawshank.mp4', 'Tim Robbins', 'Frank Darabont', 9.3),
('Interstellar', 'A team of explorers travel through a wormhole in space.', 'Sci-Fi', 2014, 'interstellar.jpg', 'https://example.com/interstellar.mp4', 'Matthew McConaughey', 'Christopher Nolan', 8.6),
('The Godfather', 'The aging patriarch of an organized crime dynasty.', 'Crime', 1972, 'godfather.jpg', 'https://example.com/godfather.mp4', 'Marlon Brando', 'Francis Ford Coppola', 9.2),
('Pulp Fiction', 'The lives of two mob hitmen, a boxer, and more intertwine.', 'Crime', 1994, 'pulp.jpg', 'https://example.com/pulp.mp4', 'John Travolta', 'Quentin Tarantino', 8.9),
('The Matrix', 'A computer hacker learns about the true nature of reality.', 'Action', 1999, 'matrix.jpg', 'https://example.com/matrix.mp4', 'Keanu Reeves', 'The Wachowskis', 8.7),
('Forrest Gump', 'The presidencies of Kennedy and Johnson unfold through the perspective of an Alabama man.', 'Drama', 1994, 'forrest.jpg', 'https://example.com/forrest.mp4', 'Tom Hanks', 'Robert Zemeckis', 8.8),
('The Dark Knight', 'Batman faces the Joker in a battle for Gotham Soul.', 'Action', 2008, 'dark_knight.jpg', 'https://example.com/dark_knight.mp4', 'Christian Bale', 'Christopher Nolan', 9.0),
('Fight Club', 'An insomniac office worker forms an underground fight club.', 'Drama', 1999, 'fight_club.jpg', 'https://example.com/fight_club.mp4', 'Brad Pitt', 'David Fincher', 8.8),
('Goodfellas', 'The story of Henry Hill and his life in the mob.', 'Crime', 1990, 'goodfellas.jpg', 'https://example.com/goodfellas.mp4', 'Robert De Niro', 'Martin Scorsese', 8.7);

-- Mark some of these new movies as premium
UPDATE movies SET is_premium = 1 WHERE title IN ('Inception', 'The Dark Knight', 'Interstellar');

-- Check what we have
SELECT 'Movies Count' as Info, COUNT(*) as Count FROM movies
UNION ALL
SELECT 'Premium Movies', COUNT(*) FROM movies WHERE is_premium = 1
UNION ALL
SELECT 'Notifications', COUNT(*) FROM notifications
UNION ALL
SELECT 'Transactions', COUNT(*) FROM transactions
UNION ALL
SELECT 'Premium Users', COUNT(*) FROM users WHERE subscription_status = 'premium';

-- Show all genres available
SELECT DISTINCT genre FROM movies ORDER BY genre;

-- Show all years available
SELECT DISTINCT year FROM movies ORDER BY year DESC;
