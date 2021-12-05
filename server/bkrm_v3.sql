drop database retail;
create database retail;
use retail;

create table if not exists `stores` (
    `id` int unsigned key auto_increment,
    `name` VARCHAR(255) not null

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

create table if not exists `branches` (
    `id` int unsigned key auto_increment,
    `name` varchar(255) not null,
    `address` varchar(255) not null,
    `deleted` boolean not null default false,
    `created_date` datetime not null default current_timestamp(),
    `store_id` int unsigned not null,

    constraint `fk_branch_store` foreign key (`store_id`) references `stores` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

create table if not exists `users` (
    `id` int unsigned key auto_increment,
    `name` varchar(255) not null,
    `password` varchar(255) not null,
    `avatar_url` varchar(255),
    `email` varchar(255) not null,
    `phone` varchar(20),
    `birthday` date,
    `gender` enum("male", "female"),
    `status` enum("enabled", "disabled") default "enabled",
    `store_id` int unsigned not null,
    `is_owner` boolean not null default false,

    constraint `fk_user_store` foreign key (`store_id`) references `stores` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

create table if not exists `employments` (
    `id` int unsigned key auto_increment,
    `user_id` int unsigned not null,
    `branch_id` int unsigned not null,
    `from` date default current_timestamp(),
    `to` date,

    constraint `fk_employment_user` foreign key (`user_id`) references `users` (`id`),
    constraint `fk_employment_branch` foreign key (`branch_id`) references `branches` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

create table if not exists `employment_roles` (
    `employment_id` int unsigned not null,
    `role` varchar(255) not null,

    constraint `fk_role_employment` foreign key (`employment_id`) references `employments` (`id`) ,
    primary key (`employment_id`, `role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

create table if not exists `shifts` (
    `name` varchar(255) key not null,
    `start_hour` time not null,
    `end_hour` time not null
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

create table if not exists `work_schedules` (
    `branch_id` int unsigned not null,
    `date` date not null,
    `user_id` int unsigned not null,
    `is_absent` boolean default false,
    `note` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

create table if not exists `roles` (
    `id` text not null
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `jwt_info` (
  `id` int unsigned key NOT NULL auto_increment,
  `user_id` int unsigned NOT NULL,
  `token` text NOT NULL,
  `is_invalidated` tinyint(1) NOT NULL DEFAULT 0,
  `created_datetime` datetime NOT NULL DEFAULT current_timestamp(),

  constraint `fk_jwt_info_users` foreign key (`user_id`) references `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

insert into `roles` values ('managing'), ('selling'), ('purchasing'), ('reporting');
