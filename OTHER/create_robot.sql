CREATE TABLE `PREFIX_robot` (
`board_id` INT NOT NULL,
`hash` VARCHAR( 40 ) NOT NULL
);

CREATE TABLE `PREFIX_mutes` (
`board_id` INT NOT NULL,
`ip` VARCHAR( 15 ) NOT NULL,
`time` INT NOT NULL
);