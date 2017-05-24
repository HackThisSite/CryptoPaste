#
# Remember, if you set a prefix here, also set it in config.ini under db.table_prefix
#
CREATE TABLE `cryptopaste` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` int(11) NOT NULL,
  `expiry` int(11) NOT NULL,
  `views` int(11) NOT NULL DEFAULT 0,
  `data` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
);
