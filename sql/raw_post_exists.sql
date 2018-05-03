# call raw_post_exists('e5c75456f3cf6eb285d8437b8e275b36',@out_pid); select @out_pid;
DELIMITER $$
DROP FUNCTION IF EXISTS raw_post_exists;
CREATE FUNCTION raw_post_exists(hash_value VARCHAR(64)) RETURNS INT
BEGIN
  DECLARE post_id INT DEFAULT 0;
  SELECT pid INTO post_id FROM cg_raw_posts WHERE post_hash = hash_value;
  RETURN post_id;
END;
