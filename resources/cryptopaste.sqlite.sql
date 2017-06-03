CREATE TABLE `cryptopaste` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `timestamp` INTEGER NOT NULL,
  `expiry` INTEGER NOT NULL,
  `views` INTEGER NOT NULL DEFAULT 0,
  `data` mediumtext NOT NULL
);
