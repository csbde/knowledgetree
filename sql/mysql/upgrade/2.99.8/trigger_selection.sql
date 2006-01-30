CREATE TABLE `trigger_selection` (
   `event_ns` varchar(255) not null default '' UNIQUE,
   PRIMARY KEY (`event_ns`),
   `selection_ns` varchar(255) not null default ''
) TYPE=InnoDB;;
